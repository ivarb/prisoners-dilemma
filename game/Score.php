<?php
class Score
{
    const BOTH_DEFECT = -10;
    const BOTH_COOP   = 30;
    const PL_DEFECT   = 50;
    const OP_DEFECT   = -20;

    final public static function getScore(Move $player, Move $opponent)
    {
        $pm = $player->getMove();
        $om = $opponent->getMove();

        if ($pm == $om) {
            if ($pm == Move::MOVE_DEFECT) {
                return Score::BOTH_DEFECT;
            }
            return Score::BOTH_COOP;
        }
        if ($pm == Move::MOVE_DEFECT) {
            return Score::PL_DEFECT;
        }
        return Score::OP_DEFECT;
    }
}