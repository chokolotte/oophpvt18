<?php
namespace chvi17\Dice;

define("GAMEGOAL", 100);
/**
*   Class for the 100 game
*
*/
class Game100
{
    /**
    * @var integer  $nrOfPlayers       The nr of players.
    * @var Player  $players            An array of Players.
    * @var integer $currentPlayerIndex The index of the current playing player
    * @var integer $roundSum           The current player's current value to risk or save
    * @var Histogram $histogram        an array of histogram for each player
    */
    private $nrOfPlayers;
    private $players;
    private $currentPlayerIndex;
    private $roundSum;
    private $histogram;


    /**
    * Constructor to create Players for the game and initiate values
    *
    * @param int $nrOfDices, nr of Dices default 5 (for Player's constructor)
    * @param int $nrOfPlayers, nr of Players default 2
    * @return void
    */
    public function __construct($nrOfDices = 5, $nrOfPlayers = 2)
    {
        if (!is_int($nrOfDices)) {
            $nrOfDices = 5;
        }
        if (!is_int($nrOfPlayers)) {
            $nrOfPlayers = 2;
        }
        $this->players = array();
        for ($i = 0; $i < $nrOfPlayers; $i++) {
            $this->players[$i] = new Player((string)$i, $nrOfDices);
            $this->histogram[$i] = new Histogram();
            $this->histogram[$i]->injectData($this->players[$i]->getDice());
        }
        $this->nrOfPlayers = $nrOfPlayers;
        $this->currentPlayerIndex = 0;
        $this->roundSum = 0;
    }

    /**
    * Method to reset the game.
    *
    * @return void
    */
    public function reset()
    {
        for ($i = 0; $i < $this->nrOfPlayers; $i++) {
            $aPlayer = $this->players[$i];
            $aPlayer->resetResult();
            $this->histogram[$i]->resetHistogramSerie($this->players[$i]->getDice());
        }
        $this->currentPlayerIndex = 0;
        $this->roundSum = 0;
    }

    /**
    * method save
    * saves the roundSum to the players result and resets roundSum
    * checks if the GAMEGOAL is reached, and returns a string
    * @return String "Winner" or empty string
    */
    public function save()
    {
        $thePlayer = $this->players[$this->currentPlayerIndex];
        $thePlayer->addResult($this->roundSum);
        $this->roundSum = 0;
        if ($thePlayer->getResult() >= GAMEGOAL) {
            return "Winner";
        } else {
            return "";
        }
    }

    /**
    * method for setting next player
    * @return void
    */
    public function setNextPlayer()
    {
        $this->currentPlayerIndex = $this->findNextPlayer();
    }

    /**
    * method for playing the Game
    * @return int[] the players hand
    */
    public function playGame()
    {
        $playerId = $this->currentPlayerIndex;
        $this->players[$playerId]->play();
        $this->roundSum += $this->players[$playerId]->getHandSum();
        $theDice = $this->players[$playerId]->getDice();
        $this->histogram[$playerId]->injectData($theDice);
        return $this->players[$playerId]->checkHand();
    }

    /**
    * method for checking if result contain 1
    * if 1 is found, the roundsum will be reset.
    * @return int $returnValue
    */
    public function isResultToBeReset()
    {
        $returnValue = 0;
        $playerId = $this->currentPlayerIndex;

        $checkedHand = $this->players[$playerId]->checkHand();
        //if contains 1
        if (in_array(1, $checkedHand)) {
            $returnValue = 1;
            $this->roundSum = 0;
        }
        return $returnValue;
    }

    /**
    * method for checking which player should start the Game
    * checks each players topDice and returns index of player with highest value.
    * if more players have the same topValue, the first player with the value will be used.
    * @return int Index of the starting player
    */
    public function checkGameStarter()
    {
        $topIndex = 0;
        $topValue = 0;
        for ($index = 0; $index < $this->nrOfPlayers; $index++) {
            $tempTopValue = $this->players[$index]->topDice();
            if ($tempTopValue > $topValue) {
                $topValue = $tempTopValue;
                $topIndex = $index;
            }
        }
        $this->currentPlayerIndex = $topIndex;
        return $topIndex;
    }

    /**
    * method for getting a recommendation if to continue or save
    * will play safe and safe unless:
    * otherPlayerResult reached 75% and mycurrentTotal < goal
    * @param integer otherPlayerResult
    * @return String action
    */
    public function continueOrSave($otherPlayerResult)
    {
        //playing safe until otherplayer reaches 75% unless I reach the goal
        $action = "Save";
        $thePlayer = $this->players[$this->currentPlayerIndex];
        $theCurrentTotal = $thePlayer->getResult() + $this->roundSum;
        if ($theCurrentTotal < GAMEGOAL && $otherPlayerResult >= GAMEGOAL * 0.75) {
            $action = "Continue";
        }
        return $action;
    }

    /**
    * method for finding out who is next to play
    * @return int Id which player should play next
    */
    public function findNextPlayer()
    {
        $nextPlayer = 1 + $this->currentPlayerIndex;
        if ($nextPlayer == $this->nrOfPlayers) {
            $nextPlayer = 0;
        }

        return $nextPlayer;
        //return $nextPlayer == $this->nrOfPlayers ? 0 : $nextPlayer;
    }


    /**
    * method for getting nrOfPlayers
    * @return int nrOfPlayers
    */
    public function getNrOfPlayers()
    {
        return $this->nrOfPlayers;
    }

    /**
    * method for getting theCurrentPlayer
    * @return int index of currentPlayer
    */
    public function getCurrentPlayer()
    {
        return $this->currentPlayerIndex;
    }

    /**
    * method for getting a player
    * @param int index of Player
    * @return Player a Player or null if wrong playerId
    */
    public function getPlayer($playerId)
    {
        if (is_int($playerId)
            && $playerId >= 0
            && $playerId < $this->getNrOfPlayers()) {
            return $this->players[$playerId];
        } else {
            return null;
        }
    }

    /**
    * method for getting current RoundSum
    * @return int current RoundSum
    */
    public function getRoundSum()
    {
        return $this->roundSum;
    }

    /**
    *  method for setting name for a player
    * @param string $name, default Computer
    * @param integer $playerId, default 0
    * @return void
    */
    public function setPlayerName($name = "Computer", $playerId = 0)
    {
        if (is_int($playerId) && is_string($name)
        && $playerId >= 0
        && $playerId < $this->getNrOfPlayers()) {
            $this->players[$playerId]->setName($name);
        }
    }

    /**
    *  method for showing the histogram for a player
    * @param int $playerId
    * @return string histogram as text or empty string if unvalid $playerId
    */
    public function showHistogram($playerId)
    {
        $print = "";
        if (is_int($playerId)
            && $playerId >= 0
            && $playerId < $this->getNrOfPlayers()) {
            $print = $this->histogram[$playerId]->getAsText();
        }
        return $print;
    }
}
