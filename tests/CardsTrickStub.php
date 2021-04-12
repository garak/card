<?php

namespace Garak\Card\Test;

use Garak\Card\Card;
use Garak\Card\CardsTrick;
use Garak\Card\Suit;

final class CardsTrickStub extends CardsTrick
{
    public function getWinningCard(?Suit $trump): Card
    {
        return Card::fromRankSuit('4d');
    }
}
