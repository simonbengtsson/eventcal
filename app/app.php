<?php

/**
 * Remove events from $content with the status $action and returns the result
 * @param $content string
 * @param $action string STATUS_MAYBE, STATUS_GOING or STATUS_UNDECIDED
 * @return string Modified content
 */
function removeEvents($content, $action)
{
    $last = 0;
    while (strpos($content, "PARTSTAT:$action", $last + 1) !== false) {
        $pos = strpos($content, "PARTSTAT:$action", $last + 1);
        $startPos = strrpos($content, "BEGIN:VEVENT", $pos - strlen($content));
        $endPos = strpos($content, "END:VEVENT", $pos) + strlen("END:VEVENT") + 2;
        $content = substr_replace($content, '', $startPos, $endPos - $startPos);
        $last = $startPos;
    }

    return $content;
}

// Escape organizer field and remove quotes to fix for example
// Google Calendar import
function fixFields($content) {
    $startNeedle = 'ORGANIZER;CN=';
    $endNeedle = ':MAILTO:';

    $offset = 0;
    while (($pos = strpos($content, $startNeedle, $offset)) !== false) {
        $offset = $pos + 1;
        $start = $pos + strlen($startNeedle);
        $end = strpos($content, $endNeedle, $pos);
        $length = $end - $start;

        $organizer = substr($content, $start, $length);
        $organizer = str_replace('"', "", $organizer);
        $organizer = '"' . $organizer . '"';

        $content = substr_replace($content, $organizer, $start, $length);
    }

    $pattern = '/X-WR-CALNAME:.*/';
    $content = preg_replace($pattern, "X-WR-CALNAME:Facebook Events", $content,1);

    return $content;
}

/**
 * Get statuses
 * @return array The statuses that should be removed
 */
function status() {
    $types = [STATUS_MAYBE, STATUS_GOING, STATUS_UNDECIDED];
    $status = array_get($_GET, "status", STATUS_GOING . ',' . STATUS_MAYBE);
    $status = $status ? explode(',', strtoupper($status)) : [];
    foreach($status as $s) {
        if (!in_array($s, $types)) {
            http_response_code(400);
            die('Not supported status: ' . $s);
        }
    }
    return array_diff($types, $status);
}

function validateUrl($url) {
    if (!preg_match('#^https?://www\.facebook\.com#', $url)) {
        http_response_code(400);
        die("Not a valid Facebook calendar url");
    }
}

// Sometimes chrome omits the ampersand in the query string (see issue #9)
function addAmpersandIfMissing($url) {
    $pos = strpos($url, 'key=');
    if ($pos !== false && $url[$pos - 1] !== '&') {
        return substr_replace($url, '&key=', $pos, strlen('key='));
    }
    return $url;
}

$calendar = array_get($_GET, "calendar");

if ($calendar) {
    if (isset($_GET["base64"])) {
        $fbCal = base64_decode($calendar);
    } else {
        $fbCal = urldecode($calendar);
    }

    if (strpos($fbCal, 'webcal') === 0) {
        $fbCal = substr_replace($fbCal, "https", 0, strlen('webcal'));
    }

    validateUrl($fbCal);
    $fbCal = addAmpersandIfMissing($fbCal);

    // Facebook blocks requests without user agent
    $content = @file_get_contents($fbCal, false, stream_context_create(['http' => [
        'header'=>"User-Agent: eventcal.flown.io\r\n"
    ]]));

    if ($content === false) {
        http_response_code(400);
        die("Could not fetch calendar");
    }

    if (strpos($content, 'BEGIN:VCALENDAR') !== 0) {
        http_response_code(400);
        die("Calendar not valid. Facebook error: " . $content);
    }

    foreach(status() as $status) {
        $content = removeEvents($content, $status);
    }
    $content = fixFields($content);

    header('Content-Type: text/calendar;charset=utf-8');
    header('Content-Disposition: attachment;filename=calendar.ics');
    die($content);
}
