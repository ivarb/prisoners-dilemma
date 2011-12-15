<?php
interface Interface_Strategy
{
    // return new Move();
    public function getMove($iteration, Interface_Strategy $opponent);
}

abstract class Strategy
{
    private $id;
    private $score = 0;
    private $opponents  = array();

    final public function __construct()
    {
        $this->generateId();
    }

    final public function getId()
    {
        return $this->id;
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

    public function preMove(){}
    public function postMove(){}

    private function generateId()
    {
        $this->id = get_class($this) . '_' . uniqid(__CLASS__);
    }

    final protected function getHistory(Interface_Strategy $opponent)
    {
        return $this->opponents[$opponent->getId()];
    }
}
