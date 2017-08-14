#!/usr/bin/php
<?php
require_once("bootstrap.php");
require_once("database.php");

$pingKeys = $redis->keys('ping:*');
$speedTestKeys = $redis->keys('speedtest:status:*');

echo "Found " . count($pingKeys) . " pings and " . count($speedTestKeys) . " speed tests.\n";

/** @var $pdo PDO */
#$pdo->beginTransaction();
foreach($pingKeys as $key){
    $keyElem = explode(":", $key,2);
    $target = $keyElem[1];
    $stmt = $pdo->prepare('INSERT INTO pings (`target`, `time`, `latency`) VALUES (:target, :time, :latency)');

    foreach($redis->hgetall($key) as $timestamp => $latency){

        $manipulatedTimeStamp = str_replace("_", " ",$timestamp);
        $manipulatedTimeStamp = explode(" ", $manipulatedTimeStamp, 2);
        $manipulatedTimeStamp[1] = str_replace("-", ":", $manipulatedTimeStamp[1]);
        $manipulatedTimeStamp = implode(" ", $manipulatedTimeStamp);
        $time = date("Y-m-d H:i:s", strtotime($manipulatedTimeStamp));

        if($latency) {
            $stmt->bindParam('target', $target);
            $stmt->bindParam('time', $time);
            $stmt->bindParam('latency', $latency);
            $stmt->execute();
            echo "Importing ping {$key}...\n";
        }
    }
}
foreach($speedTestKeys as $key){
    $speedtest = $redis->hgetall($key);
    if($speedtest['download'] == ''){
        $speedtest['download'] = null;
    }
    if($speedtest['upload'] == ''){
        $speedtest['upload'] = null;
    }
    if($speedtest['ping'] == ''){
        $speedtest['ping'] = null;
    }
    $stmt = $pdo->prepare('INSERT INTO speedtests (`down`, `up`, `ping`, `sponsor`, `name`, `host`, `time`) VALUES (:down, :up, :ping, :sponsor, :name, :host, :time)');
    $time = date("Y-m-d H:i:s", strtotime($speedtest['timestamp']));
    $stmt->bindParam('time', $time);
    $stmt->bindParam('down', $speedtest['download']);
    $stmt->bindParam('up', $speedtest['upload']);
    $stmt->bindParam('ping', $speedtest['ping']);
    $stmt->bindParam('sponsor', $speedtest['server:sponsor']);
    $stmt->bindParam('name', $speedtest['server:name']);
    $stmt->bindParam('host', $speedtest['server:host']);
    $stmt->execute();
    echo "Importing speedtest {$key}...\n";
}
#$pdo->commit();
echo "Imported " . count($pingKeys) . " pings and " . count($speedTestKeys) . " speed tests.\n";
$keysToDelete = array_merge($pingKeys, $speedTestKeys);
if(count($keysToDelete) > 0) {
    $redis->del($keysToDelete);
}