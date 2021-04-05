<?php

namespace Garak\Card\Test;

use Garak\Card\Card;
use Garak\Card\Rank;
use Garak\Card\Suit;
use PHPUnit\Framework\TestCase;

final class CardTest extends TestCase
{
    public function testConstructor(): void
    {
        $card = new Card(new Rank('2'), new Suit('d'));
        self::assertNotNull($card);
    }

    public function testConstructFromString(): void
    {
        $card = Card::fromRankSuit('Td');
        self::assertEquals('T', $card->getRank()->getValue());
        self::assertEquals('d', $card->getSuit()->getName());
    }

    public function testGetDeck(): void
    {
        self::assertIsArray(Card::getDeck());
        self::assertIsArray(Card::getDeck(true));
    }

    public function testToString(): void
    {
        $card = new Card(new Rank('5'), new Suit('c'));
        self::assertEquals('5c', (string) $card);
    }

    public function testToText(): void
    {
        $card = new Card(new Rank('J'), new Suit('s'));
        self::assertEquals('J♠', $card->toText());
    }

    public function testToHtml(): void
    {
        $card = new Card(new Rank('K'), new Suit('h'));
        self::assertEquals('<span id="Kh" class="crd crd-K st-h">K♥</span>', $card->toHtml());
    }

    public function testToUnicode(): void
    {
        $card = new Card(new Rank('A'), new Suit('s'));
        self::assertEquals('🂡', $card->toUnicode());
    }

    public function testIsEqual(): void
    {
        $card1 = new Card(new Rank('A'), new Suit('s'));
        $card2 = new Card(new Rank('A'), new Suit('s'));
        self::assertTrue($card1->isEqual($card2));
        self::assertTrue($card2->isEqual($card1));
        $card3 = new Card(new Rank('A'), new Suit('d'));
        self::assertFalse($card1->isEqual($card3));
    }
}
