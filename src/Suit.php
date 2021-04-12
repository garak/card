<?php

namespace Garak\Card;

final class Suit
{
    /** @var array<string, string> */
    public static array $suits = [
        'c' => '♣',
        'd' => '♦',
        'h' => '♥',
        's' => '♠',
    ];

    /** @var array<string, int> */
    private static array $values = [
        'c' => 1,
        'd' => 2,
        'h' => 4,
        's' => 8,
    ];

    private ?string $name = null;

    public function __construct(string $name)
    {
        if (!isset(self::$suits[$name])) {
            throw new \InvalidArgumentException(\sprintf('Invalid suit name: %s.', $name));
        }
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function toText(): string
    {
        return $this->getSymbol();
    }

    public function getName(): ?string
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
        return $this->name === $suit->getName();
    }
}
