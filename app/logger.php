<?php

/**
 * Log user agents (helpful to see what calendar software people are using)
 */

$json = json_decode(@file_get_contents('log.json'), true);
$json = $json ? $json : ["agents" => []];

$agent = array_get($_SERVER, 'HTTP_USER_AGENT');

if ($agent) {
    $agents = array_get($json, 'agents', []);
    if (!in_array($agent, $agents)) {
        $agents[] = $agent;
        $json['agents'] = $agents;
    }
}

file_put_contents('log.json', json_encode($json, JSON_PRETTY_PRINT));