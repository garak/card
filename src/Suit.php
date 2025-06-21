<?php

namespace Garak\Card;

final class Suit implements \Stringable
{
    private const CODES = [
        'c' => '♣️',
        'd' => '♦️',
        'h' => '♥️',
        's' => '♠️',
    ];

    /** @var array<string, string> */
    public static array $suits = [
        'c' => '♣',
        'd' => '♦',
        'h' => '♥',
        's' => '♠',
    ];

    /** @var array<string, string> */
    public static array $jokerColors = [
        'b' => 'black',
        'r' => 'red',
    ];

    /** @var array<string, int> */
    private static array $values = [
        'c' => 1,
        'd' => 2,
        'h' => 4,
        's' => 8,
        'b' => -1,
        'r' => -1,
    ];

    public function __construct(private readonly string $name)
    {
        if (!isset(self::$suits[$name]) && !isset(self::$jokerColors[$name])) {
            throw new \InvalidArgumentException(\sprintf('Invalid suit name: %s.', $name));
        }
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function toText(): string
    {
        return $this->getSymbol();
    }

    public function toUnicode(): string
    {
        return self::CODES[$this->name];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSymbol(): string
    {
        return self::$suits[$this->name];
    }

    public function getInt(): int
    {
        return self::$values[$this->name];
    }

    public function isEqual(self $suit): bool
    {
        return $this->name === $suit->name;
    }
}
