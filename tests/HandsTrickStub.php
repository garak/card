<?php

namespace Garak\Card\Test;

use Garak\Card\Hand;
use Garak\Card\HandsTrick;
use Garak\Card\Suit;

final class HandsTrickStub extends HandsTrick
{
    /** @param array<int|string, Hand> $hands */
    public function __construct(array $hands)
    {
        $this->hands = $hands;
    }

    public function getWinningHand(?Suit $suit): Hand
    {
        return \reset($this->hands);    // @phpstan-ignore-line return.type
    }
}
