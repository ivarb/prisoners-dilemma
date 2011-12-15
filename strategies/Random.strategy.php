<?php
class Random extends Strategy implements Interface_Strategy
{
    // Returns the move;
    public function getMove($iteration, Interface_Strategy $opponent)
    {
        if (rand(0,1000) & 1) {
            return new Move(Move::MOVE_DEFECT);
        }
        return new Move(Move::MOVE_COOP);
    }
}
