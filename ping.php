#!/usr/bin/php
<?php
require_once("bootstrap.php");
$dateStamp = date("Y-m-d_H-i-s");

if(isset($environment['PING_HOSTS'])){
    $ping_hosts = explode(",", $environment['PING_HOSTS']);
    foreach($ping_hosts as $ping_host) {
        $ping_host = trim($ping_host);
        $ping = exec('ping -c 1 ' . $ping_host . ' | grep time= | cut -d " " -f 7 | cut -d "=" -f2');
        $redis->hmset("ping:{$ping_host}", [$dateStamp => $ping]);
    }
}
