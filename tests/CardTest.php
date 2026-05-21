<?php

namespace Garak\Card\Test;

use Garak\Card\Card;
use Garak\Card\CardBack;
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
        self::assertEquals('2', $card->getRank()->value);
    }

    #[Test]
    public function constructFromString(): void
    {
        $card = Card::fromRankSuit('Td');
        self::assertEquals('T', $card->getRank()->value);
        self::assertEquals('d', $card->getSuit()->value);
    }

    #[Test]
    public function getDeck(): void
    {
        self::assertNotEmpty(Card::getDeck());
        self::assertNotEmpty(Card::getDeck(true));
        self::assertCount(52, Card::getDeck());
        self::assertCount(104, Card::getDeck(false, 2));
        self::assertCount(108, Card::getDeck(false, 2, true));
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

    #[Test]
    public function isSameFaceIgnoresBack(): void
    {
        $card1 = new Card(Rank::Ace, Suit::Spades);
        $card2 = new Card(Rank::Ace, Suit::Spades, CardBack::Red);
        self::assertTrue($card1->isSameFace($card2));
        self::assertTrue($card2->isSameFace($card1));
    }

    #[Test]
    public function getBackReturnsNullForSingleDeck(): void
    {
        $card = new Card(Rank::Ace, Suit::Spades);
        self::assertNull($card->getBack());
    }

    #[Test]
    public function getBackReturnsCardBack(): void
    {
        $card = new Card(Rank::Ace, Suit::Spades, CardBack::Red);
        self::assertEquals(CardBack::Red, $card->getBack());
    }

    #[Test]
    public function cardsWithSameFaceAndDifferentBacksAreNotEqual(): void
    {
        $card1 = new Card(Rank::Ace, Suit::Spades, CardBack::Red);
        $card2 = new Card(Rank::Ace, Suit::Spades, CardBack::Blue);
        self::assertFalse($card1->isEqual($card2));
    }

    #[Test]
    public function cardsWithDifferentBacksButOneNullAreNotEqual(): void
    {
        $card1 = new Card(Rank::Ace, Suit::Spades);
        $card2 = new Card(Rank::Ace, Suit::Spades, CardBack::Red);
        self::assertFalse($card1->isEqual($card2));
        self::assertFalse($card2->isEqual($card1));
    }

    #[Test]
    public function cardsWithSameFaceAndBackAreEqual(): void
    {
        $card1 = new Card(Rank::Ace, Suit::Spades, CardBack::Red);
        $card2 = new Card(Rank::Ace, Suit::Spades, CardBack::Red);
        self::assertTrue($card1->isEqual($card2));
    }

    #[Test]
    public function getDeckWithSingleDeckHasNoBack(): void
    {
        $deck = Card::getDeck(false, 1);
        foreach ($deck as $card) {
            self::assertNull($card->getBack());
        }
    }

    #[Test]
    public function getDeckWithMultipleDecksHasDifferentBacks(): void
    {
        $deck = Card::getDeck(false, 2);
        $redCards = \array_filter($deck, static fn (Card $card) => CardBack::Red === $card->getBack());
        $blueCards = \array_filter($deck, static fn (Card $card) => CardBack::Blue === $card->getBack());
        self::assertCount(52, $redCards);
        self::assertCount(52, $blueCards);
    }

    #[Test]
    public function getDeckWithMultipleDecksAndJokersKeepsBacks(): void
    {
        $deck = Card::getDeck(false, 2, true);
        $redCards = \array_filter($deck, static fn (Card $card) => CardBack::Red === $card->getBack());
        $blueCards = \array_filter($deck, static fn (Card $card) => CardBack::Blue === $card->getBack());
        self::assertCount(54, $redCards);
        self::assertCount(54, $blueCards);
    }
}
