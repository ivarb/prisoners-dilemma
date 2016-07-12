<?php
// Constants
const MODE = 'life';
const ITERATIONS = 10;
const STRAT_COUNT = 10;
const STRAT_FACTOR = null; // integer or null
const STRATS = [
    'TitTat',
    'TitForTat',
    'AlwaysCoop',
    'AlwaysDefect',
    'Grudger',
    'ReverseTitTat',
    'Random',
];
const STRAT_RAND = false;

// =============================================================================
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

    // Load strats
    $strategies = Loader::load();
    if (!is_array($strategies) || !count($strategies)) {
        throw new Exception('No strategies found');
    }

    // Setup game
    $game = new Game($strategies);

    // Build strategy pool
    $pool = [];
    foreach (STRATS as $strat) {
        $f = is_null(STRAT_FACTOR) ? 1 : (float) (rand(1, STRAT_FACTOR) / 10);
        $pool[$strat] = (int) round(STRAT_COUNT * $f);
    }
    $game->buildPool($pool, STRAT_RAND);

    // Set game mode
    $game->setMode(MODE);

    // Start game
    $game->run(ITERATIONS);

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
