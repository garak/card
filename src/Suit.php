<?php

namespace Garak\Card;

enum Suit: string
{
    case Clubs = 'c';
    case Diamonds = 'd';
    case Hearts = 'h';
    case Spades = 's';
    case BlackJoker = 'b';
    case RedJoker = 'r';

    public function toText(): string
    {
        return $this->getSymbol();
    }

    #[\Deprecated('Call $suit->value directly')]
    public function getName(): string
    {
        return $this->value;
    }

    public function toUnicode(): string
    {
        return match ($this) {
            self::Clubs => '♣️',
            self::Diamonds => '♦️',
            self::Hearts => '♥️',
            self::Spades => '♠️',
            default => throw new \LogicException(\sprintf('Suit %s has no unicode representation.', $this->value)),
        };
    }

    public function getSymbol(): string
    {
        return match ($this) {
            self::Clubs => '♣',
            self::Diamonds => '♦',
            self::Hearts => '♥',
            self::Spades => '♠',
            default => throw new \LogicException(\sprintf('Suit %s has no symbol.', $this->value)),
        };
    }

    public function getInt(): int
    {
        return match ($this) {
            self::Clubs => 1,
            self::Diamonds => 2,
            self::Hearts => 4,
            self::Spades => 8,
            self::BlackJoker, self::RedJoker => -1,
        };
    }

    public function isEqual(self $suit): bool
    {
        return $this === $suit;
    }
}
