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
        return match ($this) {
            self::BlackJoker, self::RedJoker => $this->value,
            default => $this->getSymbol(),
        };
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

    /**
     * @deprecated use a SuitOrder to compare suits
     */
    #[\Deprecated('Use a SuitOrder to compare suits')]
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

    public function getColor(): Color
    {
        return match ($this) {
            self::Diamonds, self::Hearts, self::RedJoker => Color::Red,
            self::Clubs, self::Spades, self::BlackJoker => Color::Black,
        };
    }

    public function isJoker(): bool
    {
        return self::BlackJoker === $this || self::RedJoker === $this;
    }

    /**
     * The four regular suits: clubs, diamonds, hearts, spades.
     *
     * @return list<self>
     */
    public static function regular(): array
    {
        return [self::Clubs, self::Diamonds, self::Hearts, self::Spades];
    }

    /**
     * The two joker suits: black and red.
     *
     * @return list<self>
     */
    public static function jokers(): array
    {
        return [self::BlackJoker, self::RedJoker];
    }

    public function isEqual(self $suit): bool
    {
        return $this === $suit;
    }
}
