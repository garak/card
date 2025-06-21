<?php

namespace Garak\Card\Test;

use Garak\Card\Suit;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SuitTest extends TestCase
{
    #[Test]
    public function constructor(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Suit('invalid');
    }

    #[Test]
    public function getInt(): void
    {
        $suit = new Suit('d');
        self::assertEquals(2, $suit->getInt());
    }

    #[Test]
    public function toUnicode(): void
    {
        $suit = new Suit('d');
        self::assertEquals('♦️', $suit->toUnicode());
    }
}
