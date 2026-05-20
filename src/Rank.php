<?php

namespace Garak\Card;

enum Rank: string
{
    case Two = '2';
    case Three = '3';
    case Four = '4';
    case Five = '5';
    case Six = '6';
    case Seven = '7';
    case Eight = '8';
    case Nine = '9';
    case Ten = 'T';
    case Jack = 'J';
    case Queen = 'Q';
    case King = 'K';
    case Ace = 'A';
    case Joker = 'w';

    public function toText(): string
    {
        return $this->value;
    }

    #[\Deprecated('Call $rank->value directly')]
    public function getValue(): string
    {
        return $this->value;
    }

    public function getInt(): int
    {
        return [
            '2' => 2,
            '3' => 3,
            '4' => 4,
            '5' => 5,
            '6' => 6,
            '7' => 7,
            '8' => 8,
            '9' => 9,
            'T' => 10,
            'J' => 11,
            'Q' => 12,
            'K' => 13,
            'A' => 14,
            'w' => -1,
        ][$this->value];
    }

    public function isEqual(self $rank): bool
    {
        return $this === $rank;
    }
}
