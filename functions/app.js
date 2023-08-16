const STATUS_MAYBE = 'TENTATIVE'; // This is also the status for "interested"
const STATUS_GOING = 'ACCEPTED';
const STATUS_UNDECIDED = 'NEEDS-ACTION';

export async function generateCalendar(calendar, isBase64, statusParam) {
    if (!calendar) {
        res.status(400).send('Calendar parameter is missing');
        return;
    }

    let fbCal;
    if (isBase64) {
        fbCal = Buffer.from(calendar, 'base64').toString();
    } else {
        fbCal = decodeURIComponent(calendar);
    }

    if (fbCal.startsWith('webcal')) {
        fbCal = fbCal.replace('webcal', 'https');
    }

    validateUrl(fbCal);
    fbCal = addAmpersandIfMissing(fbCal);

    // Fetching the Facebook calendar
    const response = await fetch(fbCal, {
        headers: {
            'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36',
            'Sec-Fetch-Site': 'none'
        }
    });

    if (!response.ok) {
        res.status(400).send('Could not fetch calendar');
        return;
    }

    let content = await response.text();
    if (!content.startsWith('BEGIN:VCALENDAR')) {
        res.status(400).send('Calendar not valid. Facebook error: ' + content);
        return;
    }

    const statuses = getStatuses(statusParam);
    for (const status of statuses) {
        content = removeEvents(content, status);
    }
    content = fixFields(content);
    return content;
}

// Remove events from content with the status action and returns the result
function removeEvents(content, action) {
    let last = 0;
    while (content.indexOf("PARTSTAT:" + action, last + 1) !== -1) {
        let pos = content.indexOf("PARTSTAT:" + action, last + 1);
        let startPos = content.lastIndexOf("BEGIN:VEVENT", pos);
        let endPos = content.indexOf("END:VEVENT", pos) + "END:VEVENT".length + 2;
        content = content.slice(0, startPos) + content.slice(endPos);
        last = startPos;
    }
    return content;
}

// Escape organizer field and remove quotes to fix for example
// Google Calendar import
function fixFields(content) {
    const startNeedle = 'ORGANIZER;CN=';
    const endNeedle = ':MAILTO:';
    let offset = 0;
    let pos;
    while ((pos = content.indexOf(startNeedle, offset)) !== -1) {
        offset = pos + 1;
        let start = pos + startNeedle.length;
        let end = content.indexOf(endNeedle, pos);
        let length = end - start;

        let organizer = content.slice(start, start + length);
        organizer = organizer.replace(/"/g, '');
        organizer = '"' + organizer + '"';

        content = content.slice(0, start) + organizer + content.slice(start + length);
    }
    content = content.replace(/X-WR-CALNAME:.*/, "X-WR-CALNAME:Facebook Events");
    return content;
}

// Get statuses
function getStatuses(queryStatus) {
    const types = [STATUS_MAYBE, STATUS_GOING, STATUS_UNDECIDED];
    let status = queryStatus ? queryStatus.split(',') : [STATUS_GOING, STATUS_MAYBE];
    status.forEach(s => {
        if (!types.includes(s)) {
            throw new Error('Not supported status: ' + s);
        }
    });
    return types.filter(type => !status.includes(type));
}

// Validate Facebook URL
function validateUrl(url) {
    const pattern = /^https?:\/\/www\.facebook\.com/;
    if (!pattern.test(url)) {
        throw new Error("Not a valid Facebook calendar url");
    }
}

// Sometimes browsers omit the ampersand in the query string
function addAmpersandIfMissing(url) {
    const pos = url.indexOf('key=');
    if (pos !== -1 && url[pos - 1] !== '&') {
        return url.slice(0, pos) + '&key=' + url.slice(pos + 'key='.length);
    }
    return url;
}