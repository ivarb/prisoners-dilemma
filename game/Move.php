<?php
class Move
{
    const MOVE_DEFECT = 'defect';
    const MOVE_COOP   = 'cooperate';

    private $move;

    public function __construct($move)
    {
        switch ($move)
        {
            case Move::MOVE_DEFECT:
                $this->move = Move::MOVE_DEFECT;
                break;

            case Move::MOVE_COOP:
                $this->move = Move::MOVE_COOP;
                break;

            default:
                throw new Exception('Invalid move');
                break;
        }
    }

    public function __toString()
    {
        return (string) $this->move;
    }

    // returns move
    public function getMove()
    {
        return $this->move;
    }
}