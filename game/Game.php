<?php
class Game
{
    private $mode = 'game';
    private $strategies;
    private $pool;
    private $world;

    private $pool_count = 0;
    private $world_len  = 0;

    private $it_cur     = 0;
    private $it_max     = 10;

    public function __construct(array $strategies = array())
    {
        if (count($strategies)) {
            $this->setStrategies($strategies);
        }
    }

    final public function setStrategies(array $strategies)
    {
        foreach ($strategies as $strategy) {
            $this->strategies[$strategy->getId()] = $strategy;
        }
    }

    final public function buildPool(array $stratTweakFactors = array())
    {
        if (!count($this->strategies)) {
            throw new Exception('No strategies set');
        }

        foreach($this->strategies as $strategy) {
            $name = get_class($strategy);
            $num  = 1;
            if (array_key_exists($name, $stratTweakFactors) && is_numeric($stratTweakFactors[$name])) {
                $num = (int) $stratTweakFactors[$name];
            }

            for ($i=0; $i < $num; $i++) {
                $this->pool[] = clone($strategy);
            }
        }

        // How many?
        $this->pool_count = count($this->pool);

        // Achieve this size
        $gridSize  = (int) ceil(sqrt($this->pool_count));
        $gridItems = $gridSize * $gridSize;
        $countDiff = $gridItems - $this->pool_count;

        // Add dummy
        for ($i=0; $i < $countDiff; $i++) {
            $this->pool[] = false;
            $this->pool_count++;
        }

        shuffle($this->pool);

        // Polulate world
        $this->populateWorld($gridSize);

        // Recalc world
        $this->recalcWorldLength();

        echo 'World build and populated with ' . $this->pool_count . ' strategies<br />';
    }

    final public function run($iterations = false)
    {
        if (is_numeric($iterations)) {
            $this->it_max = (int) $iterations;
        }

        ob_start();
        echo '<br />Running game on ' . $this->it_max . ' iterations<br />';
        echo 'Running in ' . $this->getMode() . ' mode<br /><hr>';

        while(($res = $this->round()) === true) {
            ob_flush();
        }

        echo ' - Finished: '. $res;
        ob_end_flush();
    }

    final public function round()
    {
        if (!is_array($pool = $this->getPool())) {
            return 'invalid pool';
        }

        if ($this->it_cur >= $this->it_max) {
            return 'max iterations reached';
        }

        $this->it_cur++;
        echo 'Round ' . $this->it_cur .'<br />';

        // Loop
        $roundData = array('played' => array());
        $world     = $this->world;
        // echo '<pre>';var_dump($world);
        foreach ($world as $x => $_y) {
            foreach ($_y as $y => $strategy) {
                // Empty spot
                if (!$strategy instanceof Interface_Strategy) {
                    continue;
                }

                // Get Opponent
                $strategyOpponent = $this->getRoundOpponent($world, $x, $y, $strategy, $roundData);
                if (!$strategyOpponent['strategy'] instanceof Interface_Strategy) {
                    continue;
                }

                $strategy->storeRound($this->it_cur, $strategyOpponent['strategy']);
                $strategyOpponent['strategy']->storeRound($this->it_cur, $strategy);

                $strategy->preMove();
                $strategyOpponent['strategy']->preMove();

                $sm = $strategy->getMove($this->it_cur, $strategyOpponent['strategy']);
                $om = $strategyOpponent['strategy']->getMove($this->it_cur, $strategy);

                $strategy->storeRound($this->it_cur, $strategyOpponent['strategy'], $om);
                $strategyOpponent['strategy']->storeRound($this->it_cur, $strategy, $sm);

                $strategy->postMove($strategyOpponent['strategy'], $om);
                $strategyOpponent['strategy']->postMove($strategy, $sm);

                // Round score
                $smScore = Score::getScore($sm, $om);
                $omScore = Score::getScore($om, $sm);

                echo '  '. (string) $strategy . ' does move ' . (string) $sm .' - Score: '. $smScore . '<br />';
                echo '  '. (string) $strategyOpponent['strategy'] . ' does move ' . (string) $om . ' - Score: '. $omScore . '<br /><br />';

                $strategy->addScore($smScore);
                $strategyOpponent['strategy']->addScore($omScore);

                // Store player
                $roundData['played'][] = $strategy->getId();
                $roundData['played'][] = $strategyOpponent['strategy']->getId();

                switch ($this->getMode()) {
                    case 'life':
                        // Remove/clone this strat
                        if ($smScore < 0) {
                            echo '      ' . (string) $strategy . ' has died...<br />';
                            $this->world[$x][$y] = false;
                        }

                        // Remove/clone opp strat
                        if ($omScore < 0) {
                            echo '      ' . (string) $strategyOpponent['strategy'] . ' has died...<br />';
                            $this->world[$strategyOpponent['x']][$strategyOpponent['y']] = false;
                        }

                        // Add clones to stage
                        $this->addClones($strategy, $smScore - 1);
                        $this->addClones($strategyOpponent['strategy'], $omScore - 1);
                        break;

                    default:
                    case 'game':
                        break;
                }

                unset($strategy,$strategyOpponent,$sm,$om);
            }
        }
        unset($world,$roundData);

        $this->recalcWorldLength();
        return true;
    }

    final public function setMax($max)
    {
        $this->it_max = (int) $max;
    }

    final public function getIteration()
    {
        return $this->it_cur;
    }

    final public function setMode($mode)
    {
        $this->mode = (string) $mode;
    }

    public function showWorld()
    {
        static $calls = 0;
        $calls++;

        echo '<div style="position:absolute;left:600px;top:'.($calls == 1 ? 0 : $calls*200).'px;width:400px;height:auto;"><h4>The world after ' . $this->it_cur .' iterations</h4>';

        $w = 100;
        foreach ($this->world as $x => $_y) {
            $l = (( $x + 1 )* $w + 2);
            foreach ($_y as $y => $strategy) {
                $t    = (( $y + 1 )* $w + 2);
                $name = '<ul>empty</ul>';
                $f    = 'color:black;background-color:white;';
                $score= '';

                if (is_object($strategy)) {
                    $f    = 'color:white;background-color:black;';
                    $name = get_class($strategy);
                    $score= 'Score: '.$strategy->getScore();
                }
                echo '<div style="width:'.$w.'px;height:'.$w.'px;top:'.$t.'px;left:'.$l.'px;position:absolute;border:1px solid grey;margin:1px;vertical-align:middle;overflow:hidden;'.$f.'" id="">'.$name.'<br />'.$score.'</div>';
            }
        }
        echo '</div>';
    }

    private function getMode()
    {
        return $this->mode;
    }

    private function addClones(Interface_Strategy $strategy, $amount = -1)
    {
        // TODO: add clones in life mode
        return;
    }

    private function populateWorld($gridSize)
    {
        $x = $y = $gridSize;

        // Clone
        $pool = $this->pool;
        for($px=0; $px<$x; $px++) {
            for($py=0; $py<$y; $py++) {
                $this->world[$px][$py] = array_pop($pool);
            }
        }
        unset($pool);
        // echo '<pre>';var_dump($this->world);
    }

    private function getRoundOpponent($world, $x, $y, $strategy, $roundData)
    {
        $max  = $this->world_len * $this->world_len;
        $xPos = $yPos = range(0, $this->world_len - 1);
        shuffle($xPos);
        shuffle($yPos);

        for($i=0; $i < $this->world_len; $i++) {
            $ox = $xPos[$i];

            for($j=0; $j < $this->world_len; $j++) {
                $oy = $yPos[$j];

                // echo '      {Testing opponent at pos ' . $ox .', '.$oy.' } <br />';
                // Same or no stratagy in this place
                if ($ox == $x && $oy == $y || !$world[$ox][$oy] instanceof Interface_Strategy) {
                    continue;
                }

                // Already played?
                if (in_array($world[$ox][$oy]->getId(), $roundData['played'])) {
                    continue;
                }

                return array('strategy' => $world[$ox][$oy], 'x' => $ox, 'y' => $oy);
            }
        }
    }

    private function recalcWorldLength()
    {
        $this->world_len = (int) count($this->world);
    }

    private function getPosition()
    {
        return array(rand(0, $this->world_len - 1), rand(0, $this->world_len - 1));
    }

    private function getWorld()
    {
        return $this->world;
    }

    private function getPool()
    {
        if (is_array($this->pool) && count($this->pool)) {
            return $this->pool;
        }
        return false;
    }

    private function setIteration($it)
    {
        $this->it_cur = (int) $it;
    }
}
