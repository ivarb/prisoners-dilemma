<?php
class World
{
    private $world   = array();
    private $length  = 0;
    private $empties = array();    

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
        if (array_key_exists($this->emptiesHash($x,$y), $this->empties)) {
            unset($this->empties[$this->emptiesHash($x,$y)]);
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

    public function buildRow($size, $xAs = 1)
    {
        // Recalc
        $this->calcWorldLength();
        
        $x = $y = false;

        // Set empties
        for($i=0; $i <= $size; $i++) {
            $_x = $size; $_y = $i;
            if ((bool) $xAs === false) {
                $_x = $i; $_y = $size;
            }
            if (!$this->getCoordVal($_x, $_y) instanceof Interface_Strategy) {                        
                $this->setCoordVal($_x, $_y, false);
                
                if ($x === false && $y === false) {
                    $x = $_x;
                    $y = $_y;
                }
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
                        $this->empties[$this->emptiesHash($x,$y)] = array($px, $py);
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
        $this->empties[$this->emptiesHash($x, $y)] = array($x,$y);
    }   
    
    private function emptiesHash($x, $y)
    {
        return md5($x .'-'. $y);
    }
}
