<?php

namespace Garak\Card\Test;

use Garak\Card\Card;
use Garak\Card\Rank;
use Garak\Card\Suit;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CardTest extends TestCase
{
    #[Test]
    public function constructor(): void
    {
        $card = new Card(Rank::Two, Suit::Diamonds);
        self::assertEquals('2', $card->getRank()->getValue());
    }

    #[Test]
    public function constructFromString(): void
    {
        $card = Card::fromRankSuit('Td');
        self::assertEquals('T', $card->getRank()->getValue());
        self::assertEquals('d', $card->getSuit()->getName());
    }

    #[Test]
    public function getDeck(): void
    {
        self::assertNotEmpty(Card::getDeck());
        self::assertNotEmpty(Card::getDeck(true));
        self::assertCount(52, Card::getDeck());
        self::assertCount(104, Card::getDeck(false, 2));
        self::assertCount(106, Card::getDeck(false, 2, true));
    }

    #[Test]
    public function toStringMethod(): void
    {
        $card = new Card(Rank::Five, Suit::Clubs);
        self::assertEquals('5c', (string) $card);
    }

    #[Test]
    public function toText(): void
    {
        $card = new Card(Rank::Jack, Suit::Spades);
        self::assertEquals('J♠', $card->toText());
    }

    #[Test]
    public function toHtml(): void
    {
        $card = new Card(Rank::King, Suit::Hearts);
        self::assertEquals('<span id="Kh" class="crd crd-K st-h">K♥</span>', $card->toHtml());
    }

    #[Test]
    public function toUnicode(): void
    {
        $card = new Card(Rank::Ace, Suit::Spades);
        self::assertEquals('🂡', $card->toUnicode());
    }

    #[Test]
    public function blackJoker(): void
    {
        $card = new Card(Rank::Joker, Suit::BlackJoker);
        self::assertEquals('wb', (string) $card);
        self::assertEquals('🃏', $card->toUnicode());
        self::assertEquals(Rank::Joker, $card->getRank());
        self::assertEquals(Suit::BlackJoker, $card->getSuit());
    }

    #[Test]
    public function isEqual(): void
    {
        $card1 = new Card(Rank::Ace, Suit::Spades);
        $card2 = new Card(Rank::Ace, Suit::Spades);
        self::assertTrue($card1->isEqual($card2));
        self::assertTrue($card2->isEqual($card1));
        $card3 = new Card(Rank::Ace, Suit::Diamonds);
        self::assertFalse($card1->isEqual($card3));
    }
}
