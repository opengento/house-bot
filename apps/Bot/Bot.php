<?php

namespace Opengento\HouseBot\Bot;

use PhpSlackBot\Bot as BaseBot;

class Bot extends BaseBot
{

    /**
     * Init commands list
     * @return $this
     * @throws \Exception
     */
    public function initCommands()
    {
        $this->loadCatchAllCommand(new Command\DartsCommand());

        return $this;
    }

}
