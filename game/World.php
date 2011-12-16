<?php
class World
{
    private $world  = array();
    private $length = 0;
    private $empties;

    public function setWorld(array $w)
    {
        $this->world = $w;
    }

    public function getWorld()
    {
        return $this->world;
    }

    public function setCoordVal($x, $y, $val)
    {
        $x = (int) $x;
        $y = (int) $y;
        if (!$val instanceof Interface_Strategy) {
            $this->clearSpot($x, $y);
            return;
        }
        $this->world[$x][$y] = $val;

        // Remove empty
        if (isset($this->empties[$x . '-' . $y])) {
            unset($this->empties[$x . '-' . $y]);
        }
    }

    public function getCoordVal($x, $y)
    {
        if (isset($this->world[$x][$y])) {
            return $this->world[$x][$y];
        }
        return null;
    }

    public function getGridSize($size)
    {
        if ($size) {
            return (int) ceil(sqrt($size));
        }
        return 0;
    }

    public function getLength()
    {
        return $this->length;
    }

    public function buildRow($x)
    {
        // Recalc
        $this->calcWorldLength();

        // Set empties
        for($j=0; $j <= $x; $j++) {
            if (!$this->getCoordVal($x, $j) instanceof Interface_Strategy) {
                echo $x . ' - ' . $j.'<br />';
                $this->setCoordVal($x, $j, false);
            }
        }

        // Recalc
        $this->calcWorldLength();
    }

    public function calcWorldLength()
    {
        $this->length = count($this->getWorld());
    }

    public function findRandomEmptySpot()
    {
        if (!is_array($this->empties) || !count($this->empties)) {
            $this->empties = array();

            // Init empties first time
            $x = $y = $this->getGridSize($this->getLength() * $this->getLength());
            for($px=0; $px<$x; $px++) {
                for($py=0; $py<$y; $py++) {
                    if (!$this->getCoordVal[$px][$py] instanceof Interface_Strategy) {
                        $this->empties[$x . '-' . $y] = array($px, $py);
                    }
                }
            }
        }

        // Return random empty spot
        if (count($this->empties)) {
            shuffle($this->empties);
            $spot = current($this->empties);
            reset($this->empties);
            return $spot;
        }
        // All filled up
        return false;
    }

    public function findNearestEmptySpot($x, $y)
    {
        //1's and 2's
        $spots = array(
            array($x + 1, $y),
            array($x + 1, $y + 1),
            array($x, $y + 1),
            array($x - 1, $y),
            array($x - 1, $y - 1),
            array($x, $y - 1)
        );

        shuffle($spots);

        foreach ($spots as $spot) {
            // Spot empty?
            if (!($val = $this->getCoordVal($spot[0], $spot[1])) instanceof Interface_Strategy) {
                return $val;
            }
        }
        return false;
    }

    private function clearSpot($x, $y)
    {
        // Set coord val to false in world
        $this->world[$x][$y] = false;

        // Store empty spot
        $this->empties[md5($x.'-'.$y)] = array($x,$y);
    }
}
