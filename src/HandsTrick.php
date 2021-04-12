<?php

namespace Garak\Card;

abstract class HandsTrick
{
    /** @var array<int|string, Hand> */
    protected array $hands;

    abstract public function getWinningHand(?Suit $suit): Hand;
}
