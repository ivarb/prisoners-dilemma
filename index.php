<?php
echo '<h3>Prisoners Dillemma</h3><hr>';

set_include_path(
    $_SERVER['DOCUMENT_ROOT'] . '/'
    . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT'] . '/prisoners-dillemma/'
    . PATH_SEPARATOR . get_include_path()
);

// 2 minutes max
set_time_limit(60 * 2);

require_once('game/Strategy.php');
require_once('game/Loader.php');
require_once('game/Game.php');
require_once('game/Move.php');
require_once('game/Score.php');

try {
    // Number of runs
    $iterations = 5;
    
    // Max num of strategies per strategy
    $countPerStrat = 30;    

    // Load strats
    $strategies = Loader::load();
    if (!is_array($strategies) || !count($strategies)) {
        throw new Exception('No strategies found');
    }

    // Setup game
    $game = new Game($strategies);    

    // Set random tweak factors
    $game->buildPool(array(
        'TitTat'       => rand(1, $countPerStrat),
        'TitForTat'    => rand(1, $countPerStrat),
        'AlwaysCoop'   => rand(1, $countPerStrat),
        'AlwaysDefect' => rand(1, $countPerStrat),
        'Grudger'      => rand(1, $countPerStrat),
        'ReverseTitTat'=> rand(1, $countPerStrat)
    ));

    // $game->showWorld();
    $game->setMode('game');
    $game->setMode('life');
    
    // Start game
    $game->run($iterations);
    
    // Display latest iteration
    $game->showWorld(4);
    
    // Display all :)
    /*
    for ($i=0; $i < $game->getIteration(); $i++) {
        $game->showWorld($i);
    }
    */

} catch (Exception $e) {
    die('<hr>ERROR: ' . (string) $e);
}
?>

<style type="text/css">
.world {
position:absolute;left:600px;width:400px;height:auto;
padding:5px;
}
.strat {
position:absolute;border:1px solid grey;margin:1px;vertical-align:middle;overflow:hidden;
}
.score {
}
</style>