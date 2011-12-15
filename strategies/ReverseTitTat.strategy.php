<?php
class ReverseTitTat extends Strategy implements Interface_Strategy
{

    // Returns the move;
    public function getMove($iteration, Interface_Strategy $opponent)
    {
        $history = $this->getHistory($opponent);
        if (!$history[$iteration - 1] instanceof Move) {
            return new Move(Move::MOVE_DEFECT);
        }

        // Last opps move
        $move = $history[$iteration - 1];
        if ($move->getMove() === Move::MOVE_DEFECT) {
            return new Move(Move::MOVE_COOP);

        }
        return new Move(Move::MOVE_DEFECT);
    }
}

