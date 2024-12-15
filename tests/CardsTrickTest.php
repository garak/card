<?php

namespace Garak\Card\Test;

use Garak\Card\Card;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CardsTrickTest extends TestCase
{
    #[Test]
    public function constructor(): void
    {
        $trick = new CardsTrickStub([Card::fromRankSuit('4d')]);
        self::assertEquals('4d', (string) $trick->getWinningCard(null));
    }
}
