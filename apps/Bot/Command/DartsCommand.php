<?php

namespace Opengento\HouseBot\Bot\Command;

class DartsCommand extends AbstractCommand
{

    /**
     * Database's filename
     * @var string
     */
    protected $_dbFilename = __DIR__ . '/../../../db/darts.sqlite';

    /**
     * Smileys list
     * @var array
     */
    protected $_smileys = [':joy:', ':smile:', ':heart_eyes:', ':fu:'];

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        // nothing here
    }

    /**
     * Send message with :dart: emoji at the beginning
     * @inheritDoc
     */
    public function send($channel, $username, $message)
    {
        return parent::send($channel, $username, ':dart: ' . $message);
    }

    /**
     * @inheritdoc
     */
    protected function execute($message, $context)
    {
        // Only in house channel
        if (
            !isset($message['channel'], $message['text'])
            || $message['channel'] !== $this->getHouseChannelId()
        ) {
            return;
        }
        $this->setChannel($message['channel']);

        // Help
        $regex = '`^\s*darts?\s+help$`i';
        if (preg_match($regex, $message['text'])) {
            $this->_help();
        }

        // Display the score table
        $regex = '`^\s*darts?\s+score$`i';
        if (preg_match($regex, $message['text'])) {
            $this->_displayScores();
        }

        // Reset the match
        $regex = '`^\s*darts?\s+reset$`i';
        if (preg_match($regex, $message['text'])) {
            $this->_resetMatch($message);
        }

        // Add points
        $regex = '`^\s*darts?\s+\+([0-9]+)\s*<@(U[^>]+)>.*$`i';
        if (preg_match($regex, $message['text'], $matches)) {
            $this->_addPoints($message, (string) $matches[2], (int) $matches[1]);
        }
    }

    /**
     * Reset the match
     * @param array $message
     */
    protected function _resetMatch(array $message)
    {
        $db      = $this->_openDb();
        $db->query("DELETE FROM players;");
        $db->close();
        $m = sprintf(
            "Ok <@%s>, j'ai remis à zéro la table des scores pour toi, juste pour toi %s",
            $message['user'],
            $this->_smileys[array_rand($this->_smileys)]
        );
        $this->send($this->getCurrentChannel(), null, $m);
    }

    protected function _help()
    {
        $help = <<<TXT
Pour jouer aux fléchettes c'est super simple !

Le mot clé d'activation est `darts` (ou `dart`).

Si tu fais `darts reset` ça remet à zéro la table des scores.
Si tu fais `darts score` ça te donne les scores de la partie en cours.
Si tu fais `darts +X @pseudo` alors ça ajoute `X` points à `@pseudo`.
Au cas où tu l'aurais pas compris, `X` c'est un nombre et `@pseudo` une personne, ok ? :fu:

Voilà tu peux jouer aux fléchettes ! Même bourré(e) !

:heart:
TXT;
        $this->send($this->getCurrentChannel(), null, $help);
    }

    /**
     * Add points to player
     * @param array $message
     * @param string $who
     * @param int $points
     */
    protected function _addPoints(array $message, string $who, int $points)
    {
        $db      = $this->_openDb();
        $results = $db->query("SELECT who, counter FROM players WHERE who = '$who' LIMIT 1;");
        $row     = $results->fetchArray();
        if ($row) {
            $counter = (int) $row['counter'] + (int) $points;
            $db->query("UPDATE players SET counter = $counter WHERE who = '$who';");
        } else {
            $db->query("INSERT INTO players VALUES ('$who', $points);");
        }

        $db->close();

        $m = sprintf(
            ":dart: Ok <@%s>, j'ai ajouté %d point(s) à <@%s> ! %s",
            $message['user'],
            $points,
            $who,
            $this->_smileys[array_rand($this->_smileys)]
        );
        $this->send($this->getCurrentChannel(), null, $m);
    }

    /**
     * Display the score table
     */
    protected function _displayScores()
    {
        $db = $this->_openDb();

        $lines   = ["*Score pour les fléchettes :*"];
        $results = $db->query("SELECT * FROM players ORDER by counter DESC;");
        while ($row = $results->fetchArray()) {
            $lines[] = sprintf("*<@%s>* a *%d* point(s)", $row['who'], (int) $row['counter']);
        }

        $db->close();

        // Sent message
        if (count($lines) === 1) {
            $m = "Aucun jeu en cours, dommage !";
        } else {
            $m = implode("\n", $lines);
        }
        $this->send($this->getCurrentChannel(), null, $m);
    }

    /**
     * Retrieve the Darts DB
     * @return \SQLite3
     */
    protected function _openDb()
    {
        // Setup the db?
        $setup = !is_file($this->_dbFilename);

        // Open the db
        $db = new \SQLite3($this->_dbFilename);

        // Setup if the db is new
        if ($setup) {
            $db->query("CREATE TABLE players (who VARCHAR(50), counter INTEGER(11))");
        }

        return $db;
    }

}
