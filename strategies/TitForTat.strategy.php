<?php
class TitForTat extends Strategy implements Interface_Strategy
{

    // Returns the move;
    public function getMove($iteration, Interface_Strategy $opponent)
    {
        $history = $this->getHistory($opponent);
        $k = $iteration - 1;
        if (!isset($history[$k]) || !$history[$k] instanceof Move) {
            return new Move(Move::MOVE_COOP);
        }

        // Last opps move
        return $history[$k];
    }

    // implement own spot find logic on cloning because it is a social strategy. e.g. does better when sticked togethed
    protected function getStrategySpot(World $world, $ownX, $ownY)
    {
        return $world->findNearestEmptySpot($ownX, $ownY);
    }
}
