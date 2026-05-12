<?php

namespace Garak\Card\Test;

use Garak\Card\Suit;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SuitTest extends TestCase
{
    #[Test]
    public function getInt(): void
    {
        self::assertEquals(2, Suit::Diamonds->getInt());
    }

    #[Test]
    public function toUnicode(): void
    {
        self::assertEquals('♦️', Suit::Diamonds->toUnicode());
    }
}
