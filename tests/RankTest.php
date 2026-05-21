<?php

namespace Garak\Card\Test;

use Garak\Card\Rank;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RankTest extends TestCase
{
    #[Test]
    public function getInt(): void
    {
        $rank = Rank::Jack;
        self::assertEquals(11, $rank->getInt());
    }

    #[Test]
    #[Group('legacy')]
    public function getValue(): void
    {
        $rank = Rank::Jack;
        self::assertSame('J', $rank->getValue());
    }
}
