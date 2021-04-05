<?php

namespace Garak\Card\Test;

use Garak\Card\Rank;
use PHPUnit\Framework\TestCase;

final class RankTest extends TestCase
{
    public function testConstructor(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Rank('invalid');
    }

    public function testGetInt(): void
    {
        $rank = new Rank('J');
        self::assertEquals(11, $rank->getInt());
    }
}
