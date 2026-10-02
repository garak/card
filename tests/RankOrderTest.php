<?php

namespace Garak\Card\Test;

use Garak\Card\Card;
use Garak\Card\Rank;
use Garak\Card\RankOrder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RankOrderTest extends TestCase
{
    #[Test]
    public function aceHigh(): void
    {
        $order = RankOrder::aceHigh();

        self::assertCount(13, $order);
        self::assertSame(0, $order->indexOf(Rank::Two));
        self::assertSame(12, $order->indexOf(Rank::Ace));
        self::assertSame(Rank::Two, $order->getLowest());
        self::assertSame(Rank::Ace, $order->getHighest());
    }

    #[Test]
    public function aceLow(): void
    {
        $order = RankOrder::aceLow();

        self::assertCount(13, $order);
        self::assertSame(0, $order->indexOf(Rank::Ace));
        self::assertSame(1, $order->indexOf(Rank::Two));
        self::assertSame(12, $order->indexOf(Rank::King));
        self::assertSame(Rank::Ace, $order->getLowest());
        self::assertSame(Rank::King, $order->getHighest());
    }

    #[Test]
    public function customOrder(): void
    {
        // pinochle-like: ten above the king
        $order = new RankOrder([Rank::Nine, Rank::Jack, Rank::Queen, Rank::King, Rank::Ten, Rank::Ace]);

        self::assertSame([Rank::Nine, Rank::Jack, Rank::Queen, Rank::King, Rank::Ten, Rank::Ace], $order->getRanks());
        self::assertSame(1, $order->compare(Rank::Ten, Rank::King));
        self::assertFalse($order->has(Rank::Two));
        self::assertTrue($order->has(Rank::Ten));
    }

    #[Test]
    public function jokerIsNotInTheDefaultOrders(): void
    {
        self::assertFalse(RankOrder::aceHigh()->has(Rank::Joker));
        self::assertFalse(RankOrder::aceLow()->has(Rank::Joker));
    }

    #[Test]
    public function jokerCanBeOrdered(): void
    {
        $order = new RankOrder([...RankOrder::aceHigh()->getRanks(), Rank::Joker]);

        self::assertSame(Rank::Joker, $order->getHighest());
        self::assertSame(1, $order->compareCards(Card::fromRankSuit('wb'), Card::fromRankSuit('As')));
    }

    #[Test]
    public function cannotIndexRankNotInOrder(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Rank w is not in the order.');
        RankOrder::aceHigh()->indexOf(Rank::Joker);
    }

    #[Test]
    public function cannotBeEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A rank order needs at least one rank.');
        new RankOrder([]);
    }

    #[Test]
    public function cannotRepeatRanks(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Rank A appears twice in the order.');
        new RankOrder([Rank::Ace, Rank::Two, Rank::Ace]);
    }

    #[Test]
    public function compare(): void
    {
        $order = RankOrder::aceHigh();

        self::assertSame(-1, $order->compare(Rank::Two, Rank::Ace));
        self::assertSame(0, $order->compare(Rank::Ten, Rank::Ten));
        self::assertSame(1, $order->compare(Rank::King, Rank::Queen));
        self::assertSame(1, $order->compareCards(Card::fromRankSuit('Ah'), Card::fromRankSuit('Ks')));
    }

    #[Test]
    public function nextAndPrevious(): void
    {
        $order = RankOrder::aceHigh();

        self::assertSame(Rank::Three, $order->next(Rank::Two));
        self::assertNull($order->next(Rank::Ace));
        self::assertSame(Rank::King, $order->previous(Rank::Ace));
        self::assertNull($order->previous(Rank::Two));
    }

    #[Test]
    public function highestAndLowest(): void
    {
        $order = RankOrder::aceHigh();
        $cards = [Card::fromRankSuit('5h'), Card::fromRankSuit('Kd'), Card::fromRankSuit('Ks'), Card::fromRankSuit('2c')];

        self::assertSame('Kd', (string) $order->highest($cards)); // first one on ties
        self::assertSame('2c', (string) $order->lowest($cards));
        self::assertNull($order->highest([]));
        self::assertNull($order->lowest([]));
    }
}
