<?php

namespace Garak\Card\Test;

use Garak\Card\CardsTrick;
use Garak\Card\RankOrder;

final class AceLowTrickStub extends CardsTrick
{
    protected function getRankOrder(): RankOrder
    {
        return RankOrder::aceLow();
    }
}
