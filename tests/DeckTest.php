<?php

namespace Garak\Card\Test;

use Garak\Card\Card;
use Garak\Card\CardBack;
use Garak\Card\Deck;
use Garak\Card\Rank;
use Garak\Card\Suit;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Random\Engine\Mt19937;
use Random\Randomizer;

final class DeckTest extends TestCase
{
    #[Test]
    public function standardDeck(): void
    {
        $deck = new Deck();
        $cards = $deck->getCards();

        self::assertCount(52, $deck);
        self::assertCount(52, $cards);
        self::assertSame('2c', (string) $cards[0]);
        self::assertSame('Ac', (string) $cards[12]);
        self::assertSame('2d', (string) $cards[13]);
        self::assertSame('As', (string) $cards[51]);
        self::assertNull($cards[0]->getBack());
        self::assertSame(Rank::regular(), $deck->ranks);
        self::assertSame(Suit::regular(), $deck->suits);
        self::assertSame([], $deck->backs);
    }

    #[Test]
    public function matchesCardGetDeck(): void
    {
        self::assertSame(self::strings(Card::getDeck()), self::strings((new Deck())->getCards()));
        self::assertSame(self::strings(Card::getDeck(num: 2, allowJokers: true)), self::strings((new Deck(copies: 2, jokers: 4))->getCards()));
        self::assertSame(self::strings(Card::getDeck(num: 3)), self::strings((new Deck(copies: 3))->getCards()));
    }

    #[Test]
    public function copiesGetBacksInTurn(): void
    {
        $cards = (new Deck(copies: 3))->getCards();

        self::assertCount(156, $cards);
        self::assertSame(CardBack::Red, $cards[0]->getBack());
        self::assertSame(CardBack::Blue, $cards[52]->getBack());
        self::assertSame(CardBack::Red, $cards[104]->getBack());
    }

    #[Test]
    public function jokersAlternateColorsAndAreSpreadOverCopies(): void
    {
        $cards = (new Deck(copies: 2, jokers: 3))->getCards();

        self::assertCount(107, $cards);
        self::assertSame('wbr', $cards[52]->toString(true));
        self::assertSame('wrr', $cards[53]->toString(true));
        self::assertSame('2db', $cards[54 + 13]->toString(true));
        self::assertSame('wbb', $cards[106]->toString(true));
    }

    #[Test]
    public function jokersInASingleDeck(): void
    {
        $cards = (new Deck(jokers: 2))->getCards();

        self::assertCount(54, $cards);
        self::assertSame('wb', (string) $cards[52]);
        self::assertSame('wr', (string) $cards[53]);
        self::assertNull($cards[53]->getBack());
    }

    #[Test]
    public function strippedDeck(): void
    {
        $ranks = \array_slice(Rank::regular(), 5); // seven to ace
        $deck = new Deck(ranks: $ranks);

        self::assertCount(32, $deck);
        self::assertSame('7c', (string) $deck->getCards()[0]);
        self::assertSame($ranks, $deck->ranks);
    }

    #[Test]
    public function customSuits(): void
    {
        $deck = new Deck(suits: [Suit::Hearts, Suit::Spades]);

        self::assertCount(26, $deck);
        self::assertSame('2h', (string) $deck->getCards()[0]);
        self::assertSame('As', (string) $deck->getCards()[25]);
    }

    #[Test]
    public function shoeWithoutBacks(): void
    {
        $deck = new Deck(copies: 6, backs: []);
        $cards = $deck->getCards();

        self::assertCount(312, $deck);
        self::assertNull($cards[0]->getBack());
        self::assertNull($cards[311]->getBack());
        self::assertTrue($cards[0]->isEqual($cards[52]));
    }

    #[Test]
    public function customBacks(): void
    {
        $cards = (new Deck(copies: 2, backs: [CardBack::Blue]))->getCards();

        self::assertSame(CardBack::Blue, $cards[0]->getBack());
        self::assertSame(CardBack::Blue, $cards[52]->getBack());

        $cards = (new Deck(backs: [CardBack::Red]))->getCards();
        self::assertSame(CardBack::Red, $cards[0]->getBack());
    }

    #[Test]
    public function needsAtLeastOneCopy(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A deck needs at least one copy.');
        new Deck(copies: 0);
    }

    #[Test]
    public function jokersCannotBeNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Jokers cannot be negative.');
        new Deck(jokers: -1);
    }

    #[Test]
    public function needsRanksAndSuits(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A deck needs at least one rank and one suit.');
        new Deck(ranks: []);
    }

    #[Test]
    public function jokerIsNotARank(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Jokers are not a rank of the deck: use the $jokers count.');
        new Deck(ranks: [Rank::Ace, Rank::Joker]);
    }

    #[Test]
    public function jokerIsNotASuit(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Joker suits are not a suit of the deck: use the $jokers count.');
        new Deck(suits: [Suit::Spades, Suit::RedJoker]);
    }

    #[Test]
    public function shuffleIsReproducibleWithASeed(): void
    {
        $deck = new Deck();
        $first = $deck->shuffle(new Randomizer(new Mt19937(42)));
        $second = $deck->shuffle(new Randomizer(new Mt19937(42)));

        self::assertSame([0, 51], [\array_key_first($first), \array_key_last($first)]);
        self::assertSame(self::strings($first), self::strings($second));
        self::assertNotSame(self::strings($first), self::strings($deck->getCards()));
        self::assertSame(\count($deck), \count($deck->shuffle()));
    }

    #[Test]
    public function createPileDealsInOrder(): void
    {
        $deck = new Deck();
        $pile = $deck->createPile();

        self::assertCount(52, $pile);
        self::assertSame('2c', (string) $pile->draw());
        self::assertSame('3c', (string) $pile->draw());
    }

    #[Test]
    public function createShuffledPile(): void
    {
        $deck = new Deck();
        $pile = $deck->createPile(true, new Randomizer(new Mt19937(7)));
        $expected = $deck->shuffle(new Randomizer(new Mt19937(7)));

        self::assertSame((string) $expected[0], (string) $pile->draw());
        self::assertSame((string) $expected[1], (string) $pile->draw());
    }

    /**
     * @param array<int, Card> $cards
     *
     * @return list<string>
     */
    private static function strings(array $cards): array
    {
        return \array_values(\array_map(static fn (Card $card): string => $card->toString(true), $cards));
    }
}
