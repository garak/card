<?php

namespace Garak\Card\Test;

use Garak\Card\Card;
use Garak\Card\CardBack;
use Garak\Card\Rank;
use Garak\Card\Suit;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HandTest extends TestCase
{
    #[Test]
    public function createFromDeck(): void
    {
        [$hand] = HandStub::deal();
        self::assertCount(13, $hand->getCards());
    }

    #[Test]
    public function cannotCreateStartingHandWithLessThan13Cards(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid starting hand.');
        new HandStub([], true, self::getCheck());
    }

    #[Test]
    public function handStringRepresentation(): void
    {
        $string = '6s,4h,3s,Td,6c,3d,3h,Kc,Qc,Tc,7d,2c,6d';
        $hand = HandStub::createFromString($string);
        self::assertEquals($string, (string) $hand);
    }

    #[Test]
    public function handStringRepresentationDropsBacksByDefault(): void
    {
        $hand = HandStub::createFromString('Asr,Kdb,7c', false);
        self::assertEquals('As,Kd,7c', (string) $hand);
        self::assertEquals('As,Kd,7c', $hand->toString());
    }

    #[Test]
    public function handStringRepresentationWithBacks(): void
    {
        $hand = HandStub::createFromString('Asr,Kdb,7c', false);
        self::assertEquals('Asr,Kdb,7c', $hand->toString(true));
    }

    #[Test]
    public function handStringRoundTripKeepsBacks(): void
    {
        $hand = HandStub::createFromString('Asr,Kdb', false);
        $parsed = HandStub::createFromString($hand->toString(true), false);
        self::assertEquals(CardBack::Red, $parsed->getCards()[0]->getBack());
        self::assertEquals(CardBack::Blue, $parsed->getCards()[1]->getBack());
    }

    #[Test]
    public function handTextRepresentation(): void
    {
        $hand = HandStub::createFromString('6s,4h,3s,Td,6c,3d,3h,Kc,Qc,Tc,7d,2c,6d', true, self::getCheck());
        self::assertEquals('6♠ 4♥ 3♠ T♦ 6♣ 3♦ 3♥ K♣ Q♣ T♣ 7♦ 2♣ 6♦', $hand->toText());
    }

    #[Test]
    public function handHtmlRepresentation(): void
    {
        $hand = HandStub::createFromString('6s,4h,3s,Td,6c,3d,3h,Kc,Qc,Tc,7d,2c,6d', true, self::getCheck());
        self::assertStringStartsWith('<span id="6s" class="crd crd-6 st-s">6♠</span>', $hand->toHtml());
    }

    #[Test]
    public function validHands(): void
    {
        self::assertTrue(HandStub::isValid('6s'));
        self::assertTrue(HandStub::isValid('6s,4h,3s'));
        self::assertTrue(HandStub::isValid('6sr'));
        self::assertTrue(HandStub::isValid('6sr,4hb'));
        self::assertFalse(HandStub::isValid('6'));
        self::assertFalse(HandStub::isValid('6sr4'));
    }

    #[Test]
    public function cannotPlayCardNotPresentInHand(): void
    {
        $hand = HandStub::createFromString('6s,4h,3s,Td,6c,3d,3h,Kc,Qc,Tc,7d,2c,6d');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Card 5d not present in hand (6s,4h,3s,Td,6c,3d,3h,Kc,Qc,Tc,7d,2c,6d).');
        $hand->play(Card::fromRankSuit('5d'));
    }

    #[Test]
    public function canPlayCardWithMatchingBack(): void
    {
        $hand = new HandStub([new Card(Rank::Ace, Suit::Spades, CardBack::Red)], false);
        $played = $hand->play(new Card(Rank::Ace, Suit::Spades, CardBack::Red));

        self::assertTrue($played->isEmpty());
    }

    #[Test]
    public function cannotPlayCardWithDifferentBack(): void
    {
        $hand = new HandStub([new Card(Rank::Ace, Suit::Spades, CardBack::Red)], false);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Card As not present in hand (As).');
        $hand->play(new Card(Rank::Ace, Suit::Spades, CardBack::Blue));
    }

    #[Test]
    public function countCards(): void
    {
        $hand = HandStub::createFromString('6s,4h,3s', false);
        self::assertSame(3, $hand->count());
        self::assertCount(3, $hand);
        self::assertCount(0, new HandStub([], false));
    }

    #[Test]
    public function hasCard(): void
    {
        $hand = HandStub::createFromString('6s,4h,3s', false);
        self::assertTrue($hand->has(Card::fromRankSuit('4h')));
        self::assertFalse($hand->has(Card::fromRankSuit('5d')));
    }

    #[Test]
    public function hasCardDistinguishesBacks(): void
    {
        $hand = HandStub::createFromString('Asr', false);
        self::assertTrue($hand->has(Card::fromRankSuit('Asr')));
        self::assertFalse($hand->has(Card::fromRankSuit('Asb')));
        self::assertFalse($hand->has(Card::fromRankSuit('As')));
    }

    #[Test]
    public function addCardReturnsNewHand(): void
    {
        $hand = HandStub::createFromString('6s,4h', false);
        $added = $hand->add(Card::fromRankSuit('3s'));

        self::assertNotSame($hand, $added);
        self::assertEquals('6s,4h,3s', (string) $added);
        self::assertCount(3, $added);
        self::assertEquals('6s,4h', (string) $hand);
        self::assertCount(2, $hand);
    }

    #[Test]
    public function addCardDoesNotKeepSorting(): void
    {
        $calls = 0;
        $sort = static function () use (&$calls): void { ++$calls; };
        $hand = HandStub::createFromString('6s,4h', false, null, $sort);

        $added = $hand->add(Card::fromRankSuit('3s'));
        $added->sort(null);

        self::assertSame(0, $calls);
    }

    #[Test]
    public function addCardWithSortingOverride(): void
    {
        $original = 0;
        $override = 0;
        $hand = HandStub::createFromString('6s,4h', false, null, static function () use (&$original): void { ++$original; });

        $added = $hand->add(Card::fromRankSuit('3s'), static function () use (&$override): void { ++$override; });
        $added->sort(null);

        self::assertSame(0, $original);
        self::assertSame(1, $override);
    }

    #[Test]
    public function playCardDoesNotKeepSorting(): void
    {
        $calls = 0;
        $sort = static function () use (&$calls): void { ++$calls; };
        $hand = HandStub::createFromString('6s,4h', false, null, $sort);

        $played = $hand->play(Card::fromRankSuit('6s'));
        $played->sort(null);

        self::assertSame(0, $calls);
    }

    #[Test]
    public function playCardWithSortingOverride(): void
    {
        $original = 0;
        $override = 0;
        $hand = HandStub::createFromString('6s,4h', false, null, static function () use (&$original): void { ++$original; });

        $played = $hand->play(Card::fromRankSuit('6s'), static function () use (&$override): void { ++$override; });
        $played->sort(null);

        self::assertSame(0, $original);
        self::assertSame(1, $override);
    }

    #[Test]
    public function getRandomCard(): void
    {
        $this->expectNotToPerformAssertions();
        $hand = HandStub::createFromString('6s', false);
        $hand->getRandomCard();
    }

    #[Test]
    public function getRandomCardWithSuit(): void
    {
        $hand = HandStub::createFromString('6s,4h', false);
        $card = $hand->getRandomCard(Suit::Spades);
        self::assertEquals('6s', (string) $card);
    }

    #[Test]
    public function cannotGetCardFromEmptyHand(): void
    {
        $hand = HandStub::createFromString('6s', false);
        $hand = $hand->play(Card::fromRankSuit('6s'));
        self::assertTrue($hand->isEmpty());
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('No cards left.');
        $hand->getRandomCard();
    }

    #[Test]
    public function sortingCache(): void
    {
        $hand = HandStub::createFromString('6s,4h,3s', false);
        $hand->sort(null);
        $hand->sort(null);
        $hand->sort(Suit::Spades);
        $hand->sort(Suit::Spades);
        self::assertEquals('6s,4h,3s', (string) $hand); // dummy sort, does nothing
    }

    private static function getCheck(): \Closure
    {
        return static fn (array $cards): bool => 13 === \count($cards);
    }
}
