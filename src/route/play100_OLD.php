<?php
/**
 * Specific routes for the game Play100.
 */
//var_dump(array_keys(get_defined_vars()));


/**
 * playing 100
 */
$app->router->any(["GET", "POST"], "lek/play100", function () use ($app) {
    //session_name(md5(__FILE__));
    //session_start();
    $title = "Play 100 with the computer";
    $data = [
        "title" => $title,
    ];

    //get the game if exists or create a new game
    if (isset($_SESSION["Game"])) {
        $game = $_SESSION["Game"];
    } else {
        $game = new \chvi17\Dice\Game100();
        $_SESSION["Game"] = $game;
    }

    $isReset = (isset($_GET["isReset"])) ?
    htmlentities($_GET["isReset"]) : false;

    $gameStatus= "init";
    $computerHand = 0;
    $computerSum = 0;
    $computerAction = "";
    $playerAction = "";
    $roundSum = 0;
    $player0 = $game->getPlayer(0);
    $player1 = $game->getPlayer(1);

    //init when user has pushed the start button
    if (isset($_GET["init"])) {
        $game->reset();
        $game->checkGameStarter();
        $gameStatus = "starting";
    }
    //if computer result was presented
    if (isset($_GET["ComputerAction"])) {
        $computerAction = htmlentities($_GET["ComputerAction"]);
        if ($computerAction == "Save" || $computerAction == "Reset") {
            $game->setNextPlayer();
        }
    }
    //take care of playeraction
    if (isset($_GET["PlayerAction"])) {
        $playerAction = htmlentities($_GET["PlayerAction"]);

        if ($playerAction == "Save") {
            //save and check if we have a winner
            if ($game->save() == "Winner") {
                $gameStatus = "Winner";
            } else { //no winner yet
                $game->setNextPlayer();
            }
        } elseif ($playerAction == "Reset") {
            //reset is already done
            $game->setNextPlayer();
        }
    }
    if (isset($_GET["Ending"])) {
        $game->reset();
        $gameStatus = "init";
    }
    // if to play
    if (isset($_GET["playing"])) {
        if ($gameStatus != "Winner") {
            $gameStatus = "playing";
        }
        //är det datorn som spelar?
        if ($game->getCurrentPlayer() == 0) {
            //datorn spelar
            $computerHand = $game->playGame();
            $computerSum += $player0->getHandSum();

            //om 1:a så blir det reset
            $isReset = $game->isResultToBeReset();
            if ($isReset == true) {
                $computerAction = "Reset";
                $computerSum = 0;
            } else {
                //continue or save?
                $computerAction = $game->continueOrSave($player1->getResult());
                if ($computerAction == "Save") {
                    if ($game->save() == "Winner") {
                        $gameStatus = "Winner";
                    }
                } else {
                    $computerSum = $game->getRoundSum();
                }
            }
        } else {
            //spelaren spelar
            //slå tärningarna
            $game->playGame();

            //om 1:a så blir det reset
            $isReset = $game->isResultToBeReset();
        }
    }

    //prepare data
    $data["game"] = $game;
    $data["status"] = $gameStatus;
    $data["computerHand"] = $computerHand;
    $data["computerSum"] = $computerSum;
    $data["computerAction"] = $computerAction;
    $data["player0"] = $player0;
    $data["player1"] = $player1;
    $data["isReset"] = ($isReset  == true ? "true" : "false");

    //add view and render page
    $app->view->add("lek/Game100", $data);
    $app->page->render($data);
});
