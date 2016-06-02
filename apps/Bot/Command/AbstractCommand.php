<?php

namespace Opengento\HouseBot\Bot\Command;

use PhpSlackBot\Command\BaseCommand;

abstract class AbstractCommand extends BaseCommand
{
    /**
     * House channel ID (hard coded…)
     * @var string
     */
    protected $_houseChannelId = 'C1DHP1PC2';

    /**
     * Retrieve the "house" channel's ID
     * @return string
     */
    public function getHouseChannelId()
    {
        return $this->_houseChannelId;
    }
}
