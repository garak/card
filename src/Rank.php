<?php

namespace Garak\Card;

final class Rank
{
    /** @var array<int|string, int> */
    public static array $ranks = [
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
    ];

    private string $value;

    public function __construct(string $value)
    {
        if (!isset(self::$ranks[$value]) && 'w' !== $value) {
            throw new \InvalidArgumentException('Invalid value: '.$value);
        }
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function toText(): string
    {
        return $this->value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getInt(): int
    {
        return self::$ranks[$this->value] ?? -1;
    }

    public function isEqual(self $rank): bool
    {
        return $this->value === $rank->getValue();
    }
}
