<?php
class AlwaysCoop extends Strategy implements Interface_Strategy
{
    // Returns the move;
    public function getMove($iteration, Interface_Strategy $opponent)
    {
        return new Move(Move::MOVE_COOP);
    }
}