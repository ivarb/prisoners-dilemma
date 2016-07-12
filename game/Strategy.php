<?php
interface Interface_Strategy
{
    // return new Move();
    public function getMove($iteration, Interface_Strategy $opponent);
}

abstract class Strategy
{
    private $id;
    private $color;
    private $score = 0;
    private $opponents  = array();

    final public function __construct()
    {
        $this->generateId();
        $this->generateColor();
    }

    final public function getId()
    {
        return $this->id;
    }

    final public function getColor()
    {
        return '#' . $this->color;
    }

    final public function __clone()
    {
        $this->generateId();
    }

    final public function storeRound($iteration, Interface_Strategy $opponent, $move = false)
    {
        $this->opponents[$opponent->getId()][$iteration] = $move;
    }

    final public function getScore()
    {
        return $this->score;
    }

    final public function addScore($score)
    {
        $this->score += (int) $score;
    }

    final public function __toString()
    {
        return get_class($this) . ' (' . $this->getId() . ')';
    }

    final public function getEmptySpot(World $world, $ownX, $ownY)
    {
        if (method_exists($this, 'getStrategySpot')) {
            return $this->getStrategySpot($world, $ownX, $ownY);
        }
        return false;
    }

    public function preMove(){}
    public function postMove(Interface_Strategy $opponent, Move $move){}

    private function generateId()
    {
        $this->id = get_class($this) . '_' . uniqid(__CLASS__);
    }

    private function generateColor()
    {
        $this->color  = substr(md5(get_class($this)), -6);
    }

    final protected function getHistory(Interface_Strategy $opponent)
    {
        return $this->opponents[$opponent->getId()];
    }
}
