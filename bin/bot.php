<?php

require_once __DIR__ . '/../vendor/autoload.php';

$bot = new MonsieurBiz\FlechetteBot\Bot\Bot();
$bot->setToken(trim(file_get_contents(__DIR__ . '/../TOKEN')));
$bot
    ->initCommands()
    ->run(false, false)
;
