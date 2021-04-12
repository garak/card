<?php

namespace Garak\Card\Test;

use Garak\Card\Card;
use PHPUnit\Framework\TestCase;

final class CardsTrickTest extends TestCase
{
    public function testConstructor(): void
    {
        $trick = new CardsTrickStub([Card::fromRankSuit('4d')]);
        self::assertEquals('4d', (string) $trick->getWinningCard(null));
    }
}
