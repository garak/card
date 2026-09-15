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

    #[Test]
    public function isJoker(): void
    {
        self::assertTrue(Rank::Joker->isJoker());
        self::assertFalse(Rank::Ace->isJoker());
    }

    #[Test]
    public function regular(): void
    {
        $regular = Rank::regular();

        self::assertCount(13, $regular);
        self::assertSame(Rank::Two, $regular[0]);
        self::assertSame(Rank::Ace, $regular[12]);
        self::assertNotContains(Rank::Joker, $regular);
    }
}
