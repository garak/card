<?php

namespace Garak\Card\Test;

use Garak\Card\Card;
use Garak\Card\Suit;
use Garak\Card\SuitOrder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SuitOrderTest extends TestCase
{
    #[Test]
    public function standard(): void
    {
        $order = SuitOrder::standard();

        self::assertCount(4, $order);
        self::assertSame([Suit::Clubs, Suit::Diamonds, Suit::Hearts, Suit::Spades], $order->getSuits());
        self::assertSame(0, $order->indexOf(Suit::Clubs));
        self::assertSame(3, $order->indexOf(Suit::Spades));
        self::assertFalse($order->has(Suit::BlackJoker));
    }

    #[Test]
    public function customOrder(): void
    {
        // big two: diamonds lowest, then clubs
        $order = new SuitOrder([Suit::Diamonds, Suit::Clubs, Suit::Hearts, Suit::Spades]);

        self::assertSame(-1, $order->compare(Suit::Diamonds, Suit::Clubs));
        self::assertSame(1, $order->compareCards(Card::fromRankSuit('2h'), Card::fromRankSuit('Ac')));
        self::assertSame(0, $order->compare(Suit::Hearts, Suit::Hearts));
    }

    #[Test]
    public function cannotIndexSuitNotInOrder(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Suit b is not in the order.');
        SuitOrder::standard()->indexOf(Suit::BlackJoker);
    }

    #[Test]
    public function cannotBeEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SuitOrder([]);
    }

    #[Test]
    public function cannotRepeatSuits(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Suit s appears twice in the order.');
        new SuitOrder([Suit::Spades, Suit::Spades]);
    }
}
