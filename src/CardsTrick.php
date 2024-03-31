<?php

namespace Garak\Card;

abstract class CardsTrick
{
    /** @param array<int|string, Card> $cards */
    public function __construct(protected array $cards)
    {
    }

    abstract public function getWinningCard(?Suit $trump): Card;
}
