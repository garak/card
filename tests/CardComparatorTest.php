<?php

namespace Garak\Card\Test;

use Garak\Card\Card;
use Garak\Card\CardComparator;
use Garak\Card\RankOrder;
use Garak\Card\Suit;
use Garak\Card\SuitOrder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CardComparatorTest extends TestCase
{
    #[Test]
    public function defaultIsBySuitThenAceHighAscending(): void
    {
        self::assertSame('2c,Kh,2s,As', self::sorted(new CardComparator(), 'As,2c,Kh,2s'));
    }

    #[Test]
    public function byRankThenSuitDescending(): void
    {
        // poker-like
        $comparator = new CardComparator(suitFirst: false, descending: true);

        self::assertSame('As,Ad,Kh,2c', self::sorted($comparator, '2c,As,Kh,Ad'));
    }

    #[Test]
    public function trumpIsTheHighestSuit(): void
    {
        $comparator = new CardComparator(trump: Suit::Hearts);

        self::assertSame('2c,As,2h,Ah', self::sorted($comparator, 'Ah,2h,As,2c'));
        self::assertSame('Ah,2h,As,2c', self::sorted($comparator->withDescending(), 'Ah,2h,As,2c'));
    }

    #[Test]
    public function jokersAlwaysGoLast(): void
    {
        $comparator = new CardComparator();

        self::assertSame('2c,As,wb,wr', self::sorted($comparator, 'wb,2c,wr,As'));
        self::assertSame('As,2c,wb,wr', self::sorted($comparator->withDescending(), 'wb,2c,wr,As'));
    }

    #[Test]
    public function jokersInTheRankOrderAreSortedLikeAnyRank(): void
    {
        $order = new RankOrder([...RankOrder::aceHigh()->getRanks(), \Garak\Card\Rank::Joker]);
        $comparator = new CardComparator($order, suitFirst: false, descending: true);

        self::assertSame('wb,As,2c', self::sorted($comparator, '2c,wb,As'));
    }

    #[Test]
    public function aceLowOrder(): void
    {
        $comparator = new CardComparator(RankOrder::aceLow());

        self::assertSame('As,2s,Ks', self::sorted($comparator, 'Ks,As,2s'));
    }

    #[Test]
    public function customSuitOrder(): void
    {
        $comparator = new CardComparator(suitOrder: new SuitOrder([Suit::Spades, Suit::Hearts, Suit::Diamonds, Suit::Clubs]));

        self::assertSame('2s,2h,2d,2c', self::sorted($comparator, '2c,2d,2h,2s'));
    }

    #[Test]
    public function suitsOutOfTheOrderAreNotCompared(): void
    {
        $comparator = new CardComparator(suitOrder: new SuitOrder([Suit::Spades]));

        self::assertSame('2h,2c,As', self::sorted($comparator, '2h,2c,As'));
        self::assertSame('2c,2h,As', self::sorted($comparator, '2c,2h,As'));
    }

    #[Test]
    public function withersReturnNewInstances(): void
    {
        $comparator = new CardComparator();

        self::assertNotSame($comparator, $comparator->withRankOrder(RankOrder::aceLow()));
        self::assertNotSame($comparator, $comparator->withSuitOrder(SuitOrder::standard()));
        self::assertNotSame($comparator, $comparator->withSuitFirst(false));
        self::assertNotSame($comparator, $comparator->withTrump(Suit::Spades));
        self::assertNotSame($comparator, $comparator->withDescending());
        self::assertSame('2c,Kh,2s,As', self::sorted($comparator, 'As,2c,Kh,2s'));
    }

    #[Test]
    public function usableAsCallable(): void
    {
        $cards = [Card::fromRankSuit('As'), Card::fromRankSuit('2c')];
        \usort($cards, new CardComparator());

        self::assertSame('2c', (string) $cards[0]);
        self::assertSame(1, (new CardComparator())(Card::fromRankSuit('As'), Card::fromRankSuit('2c')));
    }

    #[Test]
    public function sortDoesNotChangeTheGivenArray(): void
    {
        $cards = ['x' => Card::fromRankSuit('As'), 'y' => Card::fromRankSuit('2c')];
        $sorted = (new CardComparator())->sort($cards);

        self::assertSame(['x', 'y'], \array_keys($cards));
        self::assertSame([0, 1], \array_keys($sorted));
        self::assertSame('2c', (string) $sorted[0]);
    }

    private static function sorted(CardComparator $comparator, string $cards): string
    {
        $sorted = $comparator->sort(\array_map(static fn (string $rs): Card => Card::fromRankSuit($rs), \explode(',', $cards)));

        return \implode(',', \array_map(static fn (Card $card): string => (string) $card, $sorted));
    }
}
