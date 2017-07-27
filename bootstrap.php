<?php
require_once("vendor/autoload.php");

$environment = array_merge($_ENV, $_SERVER);
ksort($environment);

$redis = new Predis\Client([
    'scheme' => 'tcp',
    'host'   => $environment['REDIS_HOST'],
    'port'   => $environment['REDIS_PORT'],
]);


