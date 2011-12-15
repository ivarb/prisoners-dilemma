<?php
class TitTat extends Strategy implements Interface_Strategy
{

    // Returns the move;
    public function getMove($iteration, Interface_Strategy $opponent)
    {
        $history = $this->getHistory($opponent);
        if (!$history[$iteration - 1] instanceof Move) {
            return new Move(Move::MOVE_COOP);
        }

        // Last opps move
        return $history[$iteration - 1];
    }
}
