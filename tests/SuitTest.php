<?php

namespace Garak\Card\Test;

use Garak\Card\Suit;
use PHPUnit\Framework\TestCase;

final class SuitTest extends TestCase
{
    public function testConstructor(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Suit('invalid');
    }

    public function testGetInt(): void
    {
        $suit = new Suit('d');
        self::assertEquals(2, $suit->getInt());
    }
}
