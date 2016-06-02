<?php

require_once __DIR__ . '/../vendor/autoload.php';

$bot = new Opengento\HouseBot\Bot\Bot();
$bot->setToken(trim(file_get_contents(__DIR__ . '/../TOKEN')));
$bot
    ->initCommands()
    ->run(false, false)
;
