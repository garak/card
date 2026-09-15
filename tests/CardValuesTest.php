<?php

namespace Garak\Card\Test;

use Garak\Card\Card;
use Garak\Card\CardValues;
use Garak\Card\Rank;
use Garak\Card\RankOrder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CardValuesTest extends TestCase
{
    #[Test]
    public function aceHigh(): void
    {
        $values = CardValues::aceHigh();

        self::assertSame(2, $values->ofRank(Rank::Two));
        self::assertSame(10, $values->ofRank(Rank::Ten));
        self::assertSame(11, $values->ofRank(Rank::Jack));
        self::assertSame(13, $values->ofRank(Rank::King));
        self::assertSame(14, $values->of(Card::fromRankSuit('As')));
    }

    #[Test]
    public function aceLow(): void
    {
        $values = CardValues::aceLow();

        self::assertSame(1, $values->of(Card::fromRankSuit('Ah')));
        self::assertSame(2, $values->ofRank(Rank::Two));
        self::assertSame(13, $values->ofRank(Rank::King));
    }

    #[Test]
    public function fromRankOrderWithStep(): void
    {
        $values = CardValues::fromRankOrder(new RankOrder([Rank::Seven, Rank::Eight, Rank::Nine]), 10, 5);

        self::assertSame(10, $values->ofRank(Rank::Seven));
        self::assertSame(20, $values->ofRank(Rank::Nine));
    }

    #[Test]
    public function jokerHasNoValueByDefault(): void
    {
        $values = CardValues::aceHigh();

        self::assertFalse($values->has(Card::fromRankSuit('wb')));
        self::assertFalse($values->hasRank(Rank::Joker));
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Card wb has no value.');
        $values->of(Card::fromRankSuit('wb'));
    }

    #[Test]
    public function rankWithoutValueThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Rank w has no value.');
        CardValues::aceHigh()->ofRank(Rank::Joker);
    }

    #[Test]
    public function customValuesWithDefault(): void
    {
        // briscola-like points
        $values = new CardValues(['A' => 11, '3' => 10, 'K' => 4, 'Q' => 3, 'J' => 2], default: 0);

        self::assertSame(11, $values->of(Card::fromRankSuit('Ah')));
        self::assertSame(10, $values->of(Card::fromRankSuit('3c')));
        self::assertSame(0, $values->of(Card::fromRankSuit('7d')));
        self::assertTrue($values->has(Card::fromRankSuit('7d')));
        self::assertSame(0, $values->ofRank(Rank::Joker));
    }

    #[Test]
    public function cardOverridesRank(): void
    {
        // hearts: every heart is 1, the queen of spades is 13, the rest is nothing
        $values = (new CardValues(default: 0))
            ->withRank(Rank::Ace, 1)
            ->withCard(Card::fromRankSuit('Qs'), 13);
        $values = \array_reduce(
            \array_filter(Card::getDeck(), static fn (Card $card): bool => 'h' === $card->getSuit()->value),
            static fn (CardValues $values, Card $card): CardValues => $values->withCard($card, 1),
            $values,
        );

        self::assertSame(13, $values->of(Card::fromRankSuit('Qs')));
        self::assertSame(13, $values->of(Card::fromRankSuit('Qsr')));
        self::assertSame(0, $values->of(Card::fromRankSuit('Qh')) - 1);
        self::assertSame(1, $values->of(Card::fromRankSuit('Ah')));
        self::assertSame(1, $values->of(Card::fromRankSuit('Ad')));
        self::assertSame(0, $values->of(Card::fromRankSuit('Kd')));
    }

    #[Test]
    public function withersReturnNewInstances(): void
    {
        $values = CardValues::aceHigh();
        $changed = $values->withRank(Rank::Ace, 1);
        $defaulted = $values->withDefault(0);

        self::assertSame(14, $values->ofRank(Rank::Ace));
        self::assertSame(1, $changed->ofRank(Rank::Ace));
        self::assertSame(0, $defaulted->ofRank(Rank::Joker));
        self::assertNotSame($values, $changed);
    }

    #[Test]
    public function sum(): void
    {
        $cards = [Card::fromRankSuit('Ah'), Card::fromRankSuit('Kd'), Card::fromRankSuit('2c')];

        self::assertSame(29, CardValues::aceHigh()->sum($cards));
        self::assertSame(16, CardValues::aceLow()->sum($cards));
        self::assertSame(0, CardValues::aceHigh()->sum([]));
    }

    #[Test]
    public function compare(): void
    {
        $values = CardValues::aceLow();

        self::assertSame(-1, $values->compare(Card::fromRankSuit('Ah'), Card::fromRankSuit('2c')));
        self::assertSame(0, $values->compare(Card::fromRankSuit('Kh'), Card::fromRankSuit('Kc')));
        self::assertSame(1, $values->compare(Card::fromRankSuit('Th'), Card::fromRankSuit('9c')));
    }
}
