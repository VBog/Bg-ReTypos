<?php
/**
 * Example script of using etersoft/typos_client package
 */

//require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ .'/wp-load.php';
if (empty($_SERVER['REMOTE_ADDR']) || $_SERVER['REMOTE_ADDR'] != RETYPOS_SERVER_IP) exit();

use My\MyClientInterface;

$interface = new MyClientInterface();

$client = new \Etersoft\Typos\TyposClient($interface);

echo $client->run();
