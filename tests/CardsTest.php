<?php

namespace Garak\Card\Test;

use Garak\Card\Card;
use Garak\Card\CardBag;
use Garak\Card\CardComparator;
use Garak\Card\Cards;
use Garak\Card\CardValues;
use Garak\Card\Rank;
use Garak\Card\Suit;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CardsTest extends TestCase
{
    #[Test]
    public function emptyCards(): void
    {
        $cards = new Cards();

        self::assertTrue($cards->isEmpty());
        self::assertCount(0, $cards);
        self::assertSame('', (string) $cards);
        self::assertSame('', $cards->toHiddenString());
        self::assertNull($cards->first());
        self::assertNull($cards->last());
        self::assertSame([], $cards->toArray());
        self::assertSame([], Cards::fromString('')->toArray());
    }

    #[Test]
    public function fromStringAndBack(): void
    {
        $cards = Cards::fromString('Asr,Kd,2c');

        self::assertCount(3, $cards);
        self::assertSame('As,Kd,2c', (string) $cards);
        self::assertSame('Asr,Kd,2c', $cards->toString(true));
        self::assertSame('??r,??,??', $cards->toHiddenString());
        self::assertSame('As', (string) $cards->first());
        self::assertSame('2c', (string) $cards->last());
    }

    #[Test]
    public function constructorReindexesAndAcceptsIterables(): void
    {
        $cards = new Cards(['x' => Card::fromRankSuit('As'), 5 => Card::fromRankSuit('2c')]);
        self::assertSame([0, 1], \array_keys($cards->toArray()));

        $generator = (static function (): \Generator {
            yield Card::fromRankSuit('Kd');
            yield Card::fromRankSuit('3h');
        })();
        self::assertSame('Kd,3h', (string) new Cards($generator));
    }

    #[Test]
    public function iterable(): void
    {
        $strings = [];
        foreach (Cards::fromString('As,2c') as $key => $card) {
            $strings[$key] = (string) $card;
        }

        self::assertSame([0 => 'As', 1 => '2c'], $strings);
    }

    #[Test]
    public function lookups(): void
    {
        $cards = Cards::fromString('Asr,Asb,Kd,wb');

        self::assertTrue($cards->has(Card::fromRankSuit('Asr')));
        self::assertFalse($cards->has(Card::fromRankSuit('As')));
        self::assertTrue($cards->hasFace(Card::fromRankSuit('As')));
        self::assertSame(2, $cards->countFace(Card::fromRankSuit('As')));
        self::assertSame(0, $cards->countFace(Card::fromRankSuit('2c')));
        self::assertTrue($cards->hasSuit(Suit::Diamonds));
        self::assertFalse($cards->hasSuit(Suit::Hearts));
        self::assertTrue($cards->hasRank(Rank::Joker));
        self::assertFalse($cards->hasRank(Rank::Two));
    }

    #[Test]
    public function withAndWithout(): void
    {
        $cards = Cards::fromString('As,Kd');
        $more = $cards->with(Card::fromRankSuit('2c'), Card::fromRankSuit('As'));
        $less = $more->without(Card::fromRankSuit('As'));

        self::assertSame('As,Kd', (string) $cards);
        self::assertSame('As,Kd,2c,As', (string) $more);
        self::assertSame('Kd,2c,As', (string) $less);
        self::assertSame([0, 1, 2], \array_keys($less->toArray()));
    }

    #[Test]
    public function cannotRemoveMissingCard(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Card 2c not present in As,Kd.');
        Cards::fromString('As,Kd')->without(Card::fromRankSuit('2c'));
    }

    #[Test]
    public function filters(): void
    {
        $cards = Cards::fromString('As,Kd,Ad,wb,2c');

        self::assertSame('Kd,Ad', (string) $cards->ofSuit(Suit::Diamonds));
        self::assertSame('As,Ad', (string) $cards->ofRank(Rank::Ace));
        self::assertSame('As,Kd,Ad,2c', (string) $cards->regular());
        self::assertSame('wb', (string) $cards->jokers());
        self::assertSame('Kd,2c', (string) $cards->filter(static fn (Card $card): bool => Rank::Ace !== $card->getRank() && !$card->isJoker()));
    }

    #[Test]
    public function groupsBySuitAndRank(): void
    {
        $cards = Cards::fromString('As,Kd,Ad,2s');

        $bySuit = $cards->groupBySuit();
        self::assertSame(['s', 'd'], \array_keys($bySuit));
        self::assertSame('As,2s', (string) $bySuit['s']);
        self::assertSame('Kd,Ad', (string) $bySuit['d']);

        $byRank = $cards->groupByRank();
        self::assertSame(['A', 'K', 2], \array_keys($byRank));
        self::assertSame('As,Ad', (string) $byRank['A']);
        self::assertCount(1, $byRank[Rank::Two->value]);
    }

    #[Test]
    public function sort(): void
    {
        $cards = Cards::fromString('As,2c,Kh');
        $sorted = $cards->sort(new CardComparator());

        self::assertSame('As,2c,Kh', (string) $cards);
        self::assertSame('2c,Kh,As', (string) $sorted);
        self::assertSame('2c,As,Kh', (string) $cards->sort(static fn (Card $a, Card $b): int => \strcmp((string) $a, (string) $b)));
    }

    #[Test]
    public function sumAndMergeAndBag(): void
    {
        $cards = Cards::fromString('As,2c');
        $merged = $cards->merge(Cards::fromString('Kh'));

        self::assertSame(16, $cards->sum(CardValues::aceHigh()));
        self::assertSame('As,2c,Kh', (string) $merged);
        $bag = $merged->toBag();
        self::assertInstanceOf(CardBag::class, $bag);
        self::assertCount(3, $bag);
    }
}
