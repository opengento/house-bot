<?php

namespace MonsieurBiz\FlechetteBot\Bot\Command;

use PhpSlackBot\Command\BaseCommand;

class FlechetteCommand extends BaseCommand
{
    protected $_connection;

    public function __construct()
    {
        // Setup DB
        $this->_openDb()->query("CREATE TABLE players (who VARCHAR(50), counter INTEGER(11))");
    }

    protected function configure()
    {
        // nope
    }

    protected function execute($message, $context)
    {
        // Only #flechette channel
        $flechetteChannel = 'C1DBXF9GT';
        if (!isset($message['channel'], $message['text']) || $message['channel'] !== $flechetteChannel) {
            return;
        }
        $this->setChannel($message['channel']);

        # Help
        $regex = '`^\s*help\s*$`';
        if (preg_match($regex, $message['text'])) {
            $help = <<<HELP
Fuuuu you!

HELP;
            $this->send($this->getCurrentChannel(), null, $help);
            return;
        }

        $regex = '`^score$`';
        if (preg_match($regex, $message['text'])) {
            $this->_displayScores();
        }

        $regex = '`^reset$`';
        if (preg_match($regex, $message['text'])) {
            $db      = $this->_openDb();
            $db->query("DELETE FROM players;");
            $db->close();
            $smileys = [':joy:', ':smile:', ':heart_eyes:', ':fu:'];
            $m = sprintf(
                "Ok <@%s>, j'ai reset la table des scores pour toi, juste pour toi %s",
                $message['user'],
                $smileys[array_rand($smileys)]
            );
            $this->send($this->getCurrentChannel(), null, $m);
        }

        $regex = '`^\s*\+([0-9]+)\s*<@(U[^>]+)>.*$`';
        if (preg_match($regex, $message['text'], $matches)) {
            $db      = $this->_openDb();
            $results = $db->query("SELECT who, counter FROM players WHERE who = '$matches[2]' LIMIT 1;");
            $row     = $results->fetchArray();
            if ($row) {
                $counter = (int) $row['counter'] + (int) $matches[1];
                $db->query("UPDATE players SET counter = $counter WHERE who = '$matches[2]';");
            } else {
                $db->query("INSERT INTO players VALUES ('$matches[2]', $matches[1]);");
            }

            $db->close();

            $m = sprintf(
                "Ok <@%s>, j'ai ajouté %d point(s) à <@%s> ! %s",
                $message['user'],
                $matches[2],
                $smileys[array_rand($smileys)]
            );
            $this->send($this->getCurrentChannel(), null, $m);
        }
    }

    protected function _displayScores()
    {
        $db = $this->_openDb();

        $lines   = ["Score table:"];
        $results = $db->query("SELECT * FROM players ORDER by counter DESC;");
        while ($row = $results->fetchArray()) {
            $lines[] = sprintf("*<@%s>* a *%d* point(s)", $row['who'], (int) $row['counter']);
        }

        $db->close();

        $this->send($this->getCurrentChannel(), null, implode("\n", $lines));
    }

    protected function _openDb()
    {
        return new \SQLite3(__DIR__ . '/../../../db/flechettes.sqlite');
    }
}
