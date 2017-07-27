#!/usr/bin/php
<?php
require_once("bootstrap.php");

$reportFile = "speedtest-report.json";
if(file_exists($reportFile)) {
    $report = file_get_contents($reportFile);

    $report = json_decode($report);

    $dateStamp = date("Y-m-d_H-i-s", strtotime($report->timestamp));

    $dict = [
        'bytes_received' => $report->bytes_received,
        'bytes_sent' => $report->bytes_sent,
        'download' => $report->download,
        'upload' => $report->upload,
        'ping' => $report->ping,
        'timestamp' => $report->timestamp,
        'server:cc' => $report->server->cc,
        'server:country' => $report->server->country,
        'server:d' => $report->server->d,
        'server:host' => $report->server->host,
        'server:id' => $report->server->id,
        'server:lat' => $report->server->lat,
        'server:lon' => $report->server->lon,
        'server:latency' => $report->server->latency,
        'server:name' => $report->server->name,
        'server:sponsor' => $report->server->sponsor,
    ];

    $redis->hmset('speedtest:status', $dict);
    $redis->hmset('speedtest:status:' . $dateStamp, $dict);

    foreach($dict as $key => $value){
        switch($key){
            case 'download':
            case 'upload':
            case 'ping':
                $redis->hmset("speedtest:{$key}", [$dateStamp => $value]);
                break;
            default:
        }
    }
}else{
    die("Report file {$reportFile} does not exist.\n");
}