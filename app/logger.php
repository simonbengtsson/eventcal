<?php

$calendar = array_get($_REQUEST, 'calendar');
if (!$calendar) {
    // Only log when calendar request comes in
    return;
}

$debugStatsFile = '../eventcal.stats.json';
$fpStats = fopen($debugStatsFile, 'a+') ;
if(!flock($fpStats, LOCK_EX)) {
    logError("Could not lock stats file");
    flock($fpStats, LOCK_UN);
    fclose($fpStats);
    return;
}
writeStats(0);
flock($fpStats, LOCK_UN);
fclose($fpStats);

function writeStats($calCount) {
    global $calendar;
    global $debugStatsFile;

    $content = file_get_contents($debugStatsFile);

    if ($content === false) {
        logError("Content error: " . error_get_last());
        return null;
    }

    if (!$content) {
        $json = ["calendarCount" => 0, "requestCount" => 0, "lastWrite" => "", "agentCount" => 0, "agents" => []];
        $content = json_encode($json);
        if (!$content) {
            logError("Could not encode initial debug info");
            return;
        }
    }

    $json = json_decode($content, true);
    if (!$json) {
        logError("JSON decode stats error: " . jsonErrorText(json_last_error()) . " in calendar $calendar");
        return;
    }

    if (!isset($json['calendarCount']) || !isset($json['requestCount']) || !isset($json['agents'])) {
        logError("Malformed json");
        return;
    }

    $agentStr = $_SERVER['HTTP_USER_AGENT'];

    $json['agents'][$agentStr] = array_get($json['agents'], $agentStr, 0) + 1;

    $json['agentCount'] = count($json["agents"]);
    $json['requestCount'] = $json['requestCount'] + 1;
    $json['lastWrite'] = date("Y-m-d H:i:s");

    if ($calCount) {
        $json['calendarCount'] = $calCount;
    }

    $jsonStr = json_encode($json, JSON_PRETTY_PRINT);
    $testJsonRead = json_decode($jsonStr, true);
    if (!$testJsonRead) {
        logError("JSON test read before write error: " . jsonErrorText(json_last_error()));
        return;
    }

    $res = file_put_contents($debugStatsFile, $jsonStr);

    if (!$res) {
        logError("Could not write stats file");
        return;
    }
}

function logError($errorStr) {
    $errorsFilename = '../eventcal.errors.log';
    file_put_contents($errorsFilename, date("Y-m-d H:i:s") . ' ' . $errorStr . "\r\n", FILE_APPEND | LOCK_EX);
}

function currentDate() {
    $tz = 'Europe/Stockholm';
    $timestamp = time();
    $dt = new DateTime("now", new DateTimeZone($tz));
    $dt->setTimestamp($timestamp);
    return $dt->format('Y-m-d H:i');
}

function jsonErrorText($code) {
    switch ($code) {
        case JSON_ERROR_NONE:
            $errorText = 'No error (JSON_ERROR_NONE)';
            break;
        case JSON_ERROR_DEPTH:
            $errorText = 'Maximum stack depth exceeded (JSON_ERROR_DEPTH)';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $errorText = 'Underflow or the modes mismatch (JSON_ERROR_STATE_MISMATCH)';
            break;
        case JSON_ERROR_CTRL_CHAR:
            $errorText = 'Unexpected control character found (JSON_ERROR_CTRL_CHAR)';
            break;
        case JSON_ERROR_SYNTAX:
            $errorText = 'Syntax error, malformed JSON (JSON_ERROR_SYNTAX)';
            break;
        case JSON_ERROR_UTF8:
            $errorText = 'Malformed UTF-8 characters, possibly incorrectly encoded (JSON_ERROR_UTF8)';
            break;
        default:
            $errorText = 'Unknown error';
            break;
    }
    return "$code " . $errorText;
}