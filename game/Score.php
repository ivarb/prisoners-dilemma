<?php
class Score
{
    const BOTH_DEFECT = 'bd';
    const BOTH_COOP   = 'bc';
    const PL_DEFECT   = 'pd';
    const OP_DEFECT   = 'od';
    
    private static $scores = array(
        'life' => array(
            'bd' => 1,
            'bc' => 2,
            'pd' => 3,
            'od' => 0
        ),
        'game' => array(
            'bd' => -10,
            'bc' => 30,
            'pd' => 50,
            'od' => -20        
        )
    );

    final public static function getScore($mode, Move $player, Move $opponent)
    {
        $pm = $player->getMove();
        $om = $opponent->getMove();

        if ($pm == $om) {
            if ($pm == Move::MOVE_DEFECT) {
                return self::$scores[$mode][Score::BOTH_DEFECT];
            }
            return self::$scores[$mode][Score::BOTH_COOP];
        }
        if ($pm == Move::MOVE_DEFECT) {
            return self::$scores[$mode][Score::PL_DEFECT];
        }
        return self::$scores[$mode][Score::OP_DEFECT];
    }
}