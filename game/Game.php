<?php
class Game
{
    private $mode = 'game';
    
    private $strategies;
    private $tweakFact;
    private $pool;
    private $world;
    
    private $scores = array();
    private $roundWorlds = array();

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

        // Store tweak fact
        $this->tweakFact = $stratTweakFactors;
        
        $strats = array();
        foreach($this->strategies as $strategy) {
            $name = get_class($strategy);
            $num  = 1;
            if (array_key_exists($name, $this->tweakFact) && is_numeric($this->tweakFact[$name])) {
                $num = (int) $this->tweakFact[$name];
            }

            for ($i=0; $i < $num; $i++) {
                $strats[] = clone($strategy);
            }
        }                
        
        // Build pool
        $this->stratToPool($strats);
        
        // Achieve this size
        $gridSize  = $this->getGridSize($this->pool_count);        
               
        // Add randomeness
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
        echo '<h4>Round ' . $this->it_cur .'</h4>';

        // Store round worlds
        $this->roundWorlds[$this->it_cur] = $this->world;
        
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
                $smScore = Score::getScore($this->getMode(), $sm, $om);
                $omScore = Score::getScore($this->getMode(), $om, $sm);

                echo '<br />  '. (string) $strategy . ' does move ' . (string) $sm .' - Score: '. $smScore . '<br />';
                echo '  '. (string) $strategyOpponent['strategy'] . ' does move ' . (string) $om . ' - Score: '. $omScore . '<br />';
                
                // Add scores
                $strategy->addScore($smScore);
                $strategyOpponent['strategy']->addScore($omScore);
                
                $this->addScore($strategy, $smScore)
                     ->addScore($strategyOpponent['strategy'], $omScore);

                // Store player
                $roundData['played'][] = $strategy->getId();
                $roundData['played'][] = $strategyOpponent['strategy']->getId();

                // Add life to strategies
                switch ($this->getMode()) {
                    case 'life':
                        // Remove/clone this strat
                        if ($smScore <= 0) {
                            if ($this->dies()) {
                                echo    '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . (string) $strategy . ' has died...<br />';
                                $this->world[$x][$y] = false;
                            } else {
                                echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . (string) $strategy . ' has survived defect!<br />';
                            }
                        } else {
                            // Add clones to stage
                            $this->addClones($strategy, $smScore - 1);
                        }

                        // Remove/clone opp strat
                        if ($omScore <= 0) {
                            if ($this->dies()) {
                                echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . (string) $strategyOpponent['strategy'] . ' has died...<br />';
                                $this->world[$strategyOpponent['x']][$strategyOpponent['y']] = false;
                            } else {
                                echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . (string) $strategyOpponent['strategy'] . ' has survived defect!<br />';
                            }
                        } else {
                            $this->addClones($strategyOpponent['strategy'], $omScore - 1);                        
                        }
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
        
    public function showWorld($iteration = false)
    {
        static $calls = 0;
        $calls++;
        
        $it = $this->it_cur;
        if ($iteration) {
            $it = $iteration;
        }
        $world = $this->roundWorlds[$it];

        echo '<div class="world" style="top:'.($calls == 1 ? 0 : $calls*200).'px;"><h4>The world after ' . $this->it_cur .' iterations</h4>';

        $w = 100;
        foreach ($world as $x => $_y) {       
            $l = (( $x + 1 )* $w + 2) + 300;
            foreach ($_y as $y => $strategy) {                
                $t    = (( $y + 1 )* $w + 2);
                
                $name = '<ul>empty</ul>';
                $f    = 'color:black;background-color:white;';
                $score= '';

                if (is_object($strategy)) {
                    $f    = 'color:white;background-color:'.$strategy->getColor().';';
                    $name = get_class($strategy);
                    $score= 'Score: ' . $strategy->getScore();
                }
                echo '<div class="strat" style="width:'.$w.'px;height:'.$w.'px;top:'.$t.'px;left:'.$l.'px;'.$f.'" id="">'.$x.','.$y.' '.$name.'<br />'.$score.'</div>';
            }
        }
                
        // Sort scores
        $scores = $this->scores;
        usort($scores, array('Game', 'sortScore'));
        
        echo '<div class="score"><h4>Scores (top first)</h4>';
        foreach($scores as $score) {
            echo str_pad((string) $score['score'], 10, ' ', STR_PAD_RIGHT) . " : " . (string) $score['strategy'] . '<br />';
        }
        
        echo '</div><br clear="all"></div>';
       
    }
        
    public static function sortScore($a, $b)
    {
        return $a['score'] < $b['score'];
    }
    
    private function dies()
    {
        return rand(0, 1) & 1;
    }
    
    private function addScore(Interface_Strategy $strategy, $score)
    {
        $this->scores[$strategy->getId()] = array(
            'strategy' => $strategy,
            'score'    => $score + $this->scores[$strategy->getId()]['score']
        );
        return $this;
    }
    
    private function stratToPool($strategies = false)
    {
        $strats = $this->strategies;
        if (is_array($strategies)) {
            $strats = $strategies;
        }

        foreach($strats as $strategy) {
            $this->pool[] = clone($strategy);            
        }

        // How many?
        $this->pool_count = count($this->pool);
        
        // Calc this size
        $gridSize  = $this->getGridSize($this->pool_count);
        $gridItems = $gridSize * $gridSize;
        $countDiff = $gridItems - $this->pool_count;

        // Add dummy
        for ($i=0; $i < $countDiff; $i++) {
            $this->pool[] = false;
            $this->pool_count++;
        }
    }
    
    private function getMode()
    {
        return $this->mode;
    }    

    private function addClones(Interface_Strategy $strategy, $amount = 0)
    {
        $amount = (int) $amount;
        if ($amount <= 1) {
            return;
        }    
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Adding '.$amount.' clones for strategy '. (string) $strategy;              
        for($i=0; $i < $amount; $i++) {
            // Got empty spot left?
            $empty = $this->findEmptySpot();
            if (is_array($empty)) {
                list($x, $y) = $empty;
            } else {
                $x = $this->world_len; 
                $y = 0; // Next row
                
                // Prep row
                for($j=0; $j <= $this->world_len; $j++) {
                    $this->world[$x][$j] = false;
                }
            }
            
            // Add strategy
            $this->world[$x][$y] = clone($strategy);
            
            // Recalc
            $this->recalcWorldLength();
        }               
        echo '<br />';
    }
    
    private function findEmptySpot()
    {
        $x = $y = $this->getGridSize($this->world_len * $this->world_len);
        for($px=0; $px<$x; $px++) {
            for($py=0; $py<$y; $py++) {
                if (!$this->world[$px][$py] instanceof Interface_Strategy) {
                    return array($px, $py);
                }
            }
        }    
    }

    private function getGridSize($size)
    {
        if ($size) {
            return (int) ceil(sqrt($size));
        }
        return 0;
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
