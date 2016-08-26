<?php

require './../src/TestingBot/TestingBotAPI.php';

$key = '';
$secret = '';

$api = new TestingBot\TestingBotAPI($key, $secret);
var_dump($api->getJob('{session}', array('skip_fields' => 'logs')));
