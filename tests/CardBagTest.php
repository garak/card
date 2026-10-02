<?php

namespace Garak\Card\Test;

use Garak\Card\Card;
use Garak\Card\CardBag;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CardBagTest extends TestCase
{
    #[Test]
    public function emptyBag(): void
    {
        $bag = new CardBag();

        self::assertTrue($bag->isEmpty());
        self::assertCount(0, $bag);
        self::assertSame([], $bag->toArray());
        self::assertFalse($bag->has(Card::fromRankSuit('As')));
        self::assertSame(0, $bag->countOf(Card::fromRankSuit('As')));
    }

    #[Test]
    public function countsWithMultiplicity(): void
    {
        $bag = new CardBag(self::cards('As,Kd,As'));

        self::assertCount(3, $bag);
        self::assertSame(2, $bag->countOf(Card::fromRankSuit('As')));
        self::assertSame(1, $bag->countOf(Card::fromRankSuit('Kd')));
        self::assertTrue($bag->has(Card::fromRankSuit('Kd')));
        self::assertSame('As,As,Kd', self::stringify($bag->toArray()));
    }

    #[Test]
    public function backsAreTold(): void
    {
        $bag = new CardBag(self::cards('Asr,Asb,As'));

        self::assertSame(1, $bag->countOf(Card::fromRankSuit('Asr')));
        self::assertSame(1, $bag->countOf(Card::fromRankSuit('As')));
        self::assertCount(3, $bag);
    }

    #[Test]
    public function add(): void
    {
        $bag = new CardBag();
        $bag->add(Card::fromRankSuit('2c'), Card::fromRankSuit('2c'));

        self::assertSame(2, $bag->countOf(Card::fromRankSuit('2c')));
    }

    #[Test]
    public function remove(): void
    {
        $bag = new CardBag(self::cards('2c,2c,3c'));
        $bag->remove(Card::fromRankSuit('2c'), Card::fromRankSuit('3c'));

        self::assertSame(1, $bag->countOf(Card::fromRankSuit('2c')));
        self::assertFalse($bag->has(Card::fromRankSuit('3c')));
        self::assertCount(1, $bag);
    }

    #[Test]
    public function cannotRemoveMissingCard(): void
    {
        $bag = new CardBag(self::cards('2c'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Card 2cr is not in the bag.');
        $bag->remove(Card::fromRankSuit('2cr'));
    }

    #[Test]
    public function removeIsAtomic(): void
    {
        $bag = new CardBag(self::cards('2c,3c'));
        try {
            $bag->remove(Card::fromRankSuit('2c'), Card::fromRankSuit('3c'), Card::fromRankSuit('3c'));
            self::fail('An exception was expected.');
        } catch (\InvalidArgumentException $e) {
            self::assertSame('Card 3c is not in the bag.', $e->getMessage());
        }

        self::assertCount(2, $bag);
        self::assertTrue($bag->has(Card::fromRankSuit('2c')));
    }

    #[Test]
    public function diff(): void
    {
        $before = new CardBag(self::cards('5h,5d,5s,7c'));
        $after = new CardBag(self::cards('5h,5d,5s,8c,9c,9c'));

        self::assertSame('7c', self::stringify($before->diff($after)));
        self::assertSame('8c,9c,9c', self::stringify($after->diff($before)));
        self::assertSame([], $before->diff($before));
    }

    #[Test]
    public function subsetAndEquality(): void
    {
        $small = new CardBag(self::cards('5h,5d'));
        $big = new CardBag(self::cards('5d,5s,5h'));
        $same = new CardBag(self::cards('5d,5h'));

        self::assertTrue($small->isSubsetOf($big));
        self::assertFalse($big->isSubsetOf($small));
        self::assertTrue($small->equals($same));
        self::assertFalse($small->equals($big));
        self::assertFalse((new CardBag(self::cards('5h,5h')))->isSubsetOf($big));
    }

    /**
     * @return list<Card>
     */
    private static function cards(string $cards): array
    {
        return \array_map(static fn (string $rs): Card => Card::fromRankSuit($rs), \explode(',', $cards));
    }

    /**
     * @param list<Card> $cards
     */
    private static function stringify(array $cards): string
    {
        return \implode(',', \array_map(static fn (Card $card): string => $card->toString(true), $cards));
    }
}
