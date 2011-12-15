<?php
class Grudger extends Strategy implements Interface_Strategy
{
    private $defected = array();

    // Returns the move;
    public function getMove($iteration, Interface_Strategy $opponent)
    {
        $history = $this->getHistory($opponent);
        if (!$history[$iteration - 1] instanceof Move) {
            return new Move(Move::MOVE_COOP);
        }

        // If defected: defect
        if (array_key_exists($opponent->getId(), $this->defected)) {
            return new Move(Move::MOVE_DEFECT);
        }

        // Last opps move
        return $history[$iteration - 1];
    }

    public function postMove(Interface_Strategy $opponent, Move $move)
    {
        if ($move->getMove() == Move::MOVE_DEFECT) {
            $this->defected[$opponent->getId()] = $opponent;
        }
    }
}
