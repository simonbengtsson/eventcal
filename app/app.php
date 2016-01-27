<?php

const STATUS_MAYBE = 'TENTATIVE';
const STATUS_GOING = 'ACCEPTED';
const STATUS_UNDECIDED = 'NEEDS-ACTION';

function filterEvent($content, $action)
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

if (isset($_GET["calendar"])) {
    $fbCal = urldecode($_GET["calendar"]);
    if (strpos($fbCal, 'webcal') === 0) {
        $fbCal = substr_replace($fbCal, "http", 0, strlen('webcal'));
    }
    if (!preg_match('#^https?://www\.facebook\.com/ical/#', $fbCal)) {
        http_response_code(400);
        die("Not a valid Facebook calendar url");
    }
    $content = @file_get_contents($fbCal);

    if ($content === false) {
        http_response_code(400);
        die("Couldn't fetch calendar");
    }

    $content = filterEvent($content, STATUS_UNDECIDED);

    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment;filename=calendar.ics');
    die($content);
}
