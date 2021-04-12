<?php

namespace Garak\Card;

abstract class CardsTrick
{
    /** @var array<int|string, Card> */
    protected array $cards;

    /** @param array<int|string, Card> $cards */
    public function __construct(array $cards)
    {
        $this->cards = $cards;
    }

    abstract public function getWinningCard(?Suit $trump): Card;
}
