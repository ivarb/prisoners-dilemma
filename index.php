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
require_once('game/World.php');

try {
    // Load?
    if (isset($_GET['load_id'])) {
        $id   = addslashes(trim(strip_tags($_GET['load_id'])));
        $f    = $id .'.run';
        $game = Game::load($f);
        if (!$game instanceof Game) {
            throw new Exception('Cannot load '. $id);
        }
        $game->showWorld();
    }
    
    // Number of runs
    $iterations = 10;

    // Max num of strategies per strategy
    $strategyCount = 10;

    // Load strats
    $strategies = Loader::load();
    if (!is_array($strategies) || !count($strategies)) {
        throw new Exception('No strategies found');
    }

    // Setup game
    $game = new Game($strategies);

    // Set random tweak factors
    $game->buildPool(array(
        'TitTat'       => $strategyCount * 6,
        'TitForTat'    => $strategyCount,
        'AlwaysCoop'   => $strategyCount,
        'AlwaysDefect' => $strategyCount,
        'Grudger'      => $strategyCount * 1.5,
        'ReverseTitTat'=> $strategyCount,
        'Random'       => $strategyCount
    ), false);

    // $game->showWorld();
    $game->setMode('game');
    $game->setMode('life');

    // Start game
    $game->run($iterations);

    // Display latest iteration
    $game->showWorld();

    // Display all :)
    /*
    for ($i=0; $i < $game->getIteration(); $i++) {
        $game->showWorld($i);
    }
    */
    
    // Store game in runs dir
    $game->store();

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
