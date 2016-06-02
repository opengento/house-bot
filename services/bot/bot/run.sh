#!/bin/bash

DIR=/app

cd $DIR && composer install -o

php -f $DIR/bin/bot.php
