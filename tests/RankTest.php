<?php

namespace Garak\Card\Test;

use Garak\Card\Rank;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RankTest extends TestCase
{
    #[Test]
    public function cnstructor(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Rank('invalid');
    }

    #[Test]
    public function getInt(): void
    {
        $rank = new Rank('J');
        self::assertEquals(11, $rank->getInt());
    }
}
