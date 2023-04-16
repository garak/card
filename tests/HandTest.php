<?php

namespace Garak\Card\Test;

use Garak\Card\Card;
use Garak\Card\Suit;
use PHPUnit\Framework\TestCase;

final class HandTest extends TestCase
{
    public function testCreateFromDeck(): void
    {
        [$hand] = HandStub::deal();
        self::assertCount(13, $hand->getCards());
    }

    public function testCannotCreateStartingHandWithLessThan13Cards(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid starting hand.');
        new HandStub([], true, self::getCheck());
    }

    public function testHandStringRepresentation(): void
    {
        $string = '6s,4h,3s,Td,6c,3d,3h,Kc,Qc,Tc,7d,2c,6d';
        $hand = HandStub::createFromString($string);
        self::assertEquals($string, (string) $hand);
    }

    public function testHandTextRepresentation(): void
    {
        $hand = HandStub::createFromString('6s,4h,3s,Td,6c,3d,3h,Kc,Qc,Tc,7d,2c,6d', true, self::getCheck());
        self::assertEquals('6♠ 4♥ 3♠ T♦ 6♣ 3♦ 3♥ K♣ Q♣ T♣ 7♦ 2♣ 6♦', $hand->toText());
    }

    public function testHandHtmlRepresentation(): void
    {
        $hand = HandStub::createFromString('6s,4h,3s,Td,6c,3d,3h,Kc,Qc,Tc,7d,2c,6d', true, self::getCheck());
        self::assertStringStartsWith('<span id="6s" class="crd crd-6 st-s">6♠</span>', $hand->toHtml());
    }

    public function testValidHands(): void
    {
        self::assertTrue(HandStub::isValid('6s'));
        self::assertTrue(HandStub::isValid('6s,4h,3s'));
        self::assertFalse(HandStub::isValid('6'));
    }

    public function testCannotPlayCardNotPresentInHand(): void
    {
        $hand = HandStub::createFromString('6s,4h,3s,Td,6c,3d,3h,Kc,Qc,Tc,7d,2c,6d');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Card 5d not present in hand (6s,4h,3s,Td,6c,3d,3h,Kc,Qc,Tc,7d,2c,6d).');
        $hand->play(Card::fromRankSuit('5d'));
    }

    public function testGetRandomCard(): void
    {
        $hand = HandStub::createFromString('6s', false);
        $card = $hand->getRandomCard();
        self::assertNotNull($card);
    }

    public function testGetRandomCardWithSuit(): void
    {
        $hand = HandStub::createFromString('6s,4h', false);
        $card = $hand->getRandomCard(new Suit('s'));
        self::assertEquals('6s', (string) $card);
    }

    public function testCannotGetCardFromEmptyHand(): void
    {
        $hand = HandStub::createFromString('6s', false);
        $hand = $hand->play(Card::fromRankSuit('6s'));
        self::assertTrue($hand->isEmpty());
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('No cards left.');
        $hand->getRandomCard();
    }

    public function testSortingCache(): void
    {
        $hand = HandStub::createFromString('6s,4h,3s', false);
        $hand->sort(null);
        $hand->sort(null);
        $hand->sort(new Suit('s'));
        $hand->sort(new Suit('s'));
        self::assertEquals('6s,4h,3s', (string) $hand); // dummy sort, does nothing
    }

    private static function getCheck(): \Closure
    {
        return \Closure::fromCallable(fn (array $cards): bool => 13 === \count($cards));
    }
}
