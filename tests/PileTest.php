<?php

namespace Garak\Card\Test;

use Garak\Card\Card;
use Garak\Card\CardBack;
use Garak\Card\Pile;
use Garak\Card\Rank;
use Garak\Card\Suit;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Random\Engine\Mt19937;
use Random\Randomizer;

final class PileTest extends TestCase
{
    #[Test]
    public function emptyPile(): void
    {
        $pile = new Pile();

        self::assertTrue($pile->isEmpty());
        self::assertCount(0, $pile);
        self::assertNull($pile->top());
        self::assertSame([], $pile->getCards());
        self::assertSame('', (string) $pile);
    }

    #[Test]
    public function constructorReindexesCards(): void
    {
        $pile = new Pile(['a' => Card::fromRankSuit('2c'), 7 => Card::fromRankSuit('Kd')]);

        self::assertSame([0, 1], \array_keys($pile->getCards()));
        self::assertSame('2c,Kd', (string) $pile);
    }

    #[Test]
    public function createFromString(): void
    {
        $pile = Pile::createFromString('2c,Kd,As');

        self::assertCount(3, $pile);
        self::assertEquals('2c,Kd,As', (string) $pile);
        self::assertEquals('As', (string) $pile->top());
    }

    #[Test]
    public function createFromEmptyString(): void
    {
        $pile = Pile::createFromString('');

        self::assertTrue($pile->isEmpty());
    }

    #[Test]
    public function stringRepresentationDropsBacksByDefault(): void
    {
        $pile = Pile::createFromString('Asr,Kdb,7c');

        self::assertEquals('As,Kd,7c', (string) $pile);
        self::assertEquals('As,Kd,7c', $pile->toString());
        self::assertEquals('Asr,Kdb,7c', $pile->toString(true));
    }

    #[Test]
    public function stringRoundTripKeepsBacks(): void
    {
        $pile = Pile::createFromString('Asr,Kdb');
        $parsed = Pile::createFromString($pile->toString(true));

        self::assertEquals(CardBack::Red, $parsed->getCards()[0]->getBack());
        self::assertEquals(CardBack::Blue, $parsed->getCards()[1]->getBack());
    }

    #[Test]
    public function addPutsCardsOnTop(): void
    {
        $pile = Pile::createFromString('2c');
        $pile->add(Card::fromRankSuit('Kd'), Card::fromRankSuit('As'));

        self::assertCount(3, $pile);
        self::assertEquals('2c,Kd,As', (string) $pile);
        self::assertEquals('As', (string) $pile->top());
    }

    #[Test]
    public function addNothingLeavesPileUntouched(): void
    {
        $pile = Pile::createFromString('2c');
        $pile->add();

        self::assertEquals('2c', (string) $pile);
    }

    #[Test]
    public function drawRemovesTopCard(): void
    {
        $pile = Pile::createFromString('2c,Kd,As');

        self::assertEquals('As', (string) $pile->draw());
        self::assertEquals('Kd', (string) $pile->draw());
        self::assertEquals('2c', (string) $pile->draw());
        self::assertTrue($pile->isEmpty());
    }

    #[Test]
    public function drawKeepsBack(): void
    {
        $pile = Pile::createFromString('Asr');

        self::assertEquals(CardBack::Red, $pile->draw()->getBack());
    }

    #[Test]
    public function cannotDrawFromEmptyPile(): void
    {
        $pile = new Pile();

        $this->expectException(\UnderflowException::class);
        $this->expectExceptionMessage('Cannot draw from an empty pile.');
        $pile->draw();
    }

    #[Test]
    public function topDoesNotRemoveCard(): void
    {
        $pile = Pile::createFromString('2c,Kd');

        self::assertEquals('Kd', (string) $pile->top());
        self::assertEquals('Kd', (string) $pile->top());
        self::assertCount(2, $pile);
    }

    #[Test]
    public function takeAllEmptiesPile(): void
    {
        $pile = Pile::createFromString('2c,Kd,As');
        $cards = $pile->takeAll();

        self::assertCount(3, $cards);
        self::assertSame([0, 1, 2], \array_keys($cards));
        self::assertEquals('2c', (string) $cards[0]);
        self::assertEquals('As', (string) $cards[2]);
        self::assertTrue($pile->isEmpty());
        self::assertSame([], $pile->takeAll());
    }

    #[Test]
    public function shuffleKeepsSameCards(): void
    {
        $deck = Card::getDeck();
        $pile = new Pile($deck);
        $pile->shuffle();

        self::assertCount(\count($deck), $pile);
        self::assertSame([], \array_diff(\array_map('strval', $deck), \array_map('strval', $pile->getCards())));
        self::assertSame(\range(0, \count($deck) - 1), \array_keys($pile->getCards()));
    }

    #[Test]
    public function hasCard(): void
    {
        $pile = Pile::createFromString('6s,4h,3s');

        self::assertTrue($pile->has(Card::fromRankSuit('4h')));
        self::assertFalse($pile->has(Card::fromRankSuit('5d')));
        self::assertFalse((new Pile())->has(Card::fromRankSuit('4h')));
    }

    #[Test]
    public function hasCardDistinguishesBacks(): void
    {
        $pile = Pile::createFromString('Asr');

        self::assertTrue($pile->has(Card::fromRankSuit('Asr')));
        self::assertFalse($pile->has(Card::fromRankSuit('Asb')));
        self::assertFalse($pile->has(Card::fromRankSuit('As')));
        self::assertTrue($pile->has(new Card(Rank::Ace, Suit::Spades, CardBack::Red)));
    }

    #[Test]
    public function getCardsReturnsCopy(): void
    {
        $pile = Pile::createFromString('2c,Kd');
        $cards = $pile->getCards();
        $cards[] = Card::fromRankSuit('As');

        self::assertCount(2, $pile);
    }

    #[Test]
    public function createFromTop(): void
    {
        $pile = Pile::createFromTop(['a' => Card::fromRankSuit('2c'), 'b' => Card::fromRankSuit('Kd'), 'c' => Card::fromRankSuit('As')]);

        self::assertSame('As,Kd,2c', (string) $pile);
        self::assertSame('2c', (string) $pile->draw());
        self::assertSame('Kd', (string) $pile->draw());
    }

    #[Test]
    public function drawMany(): void
    {
        $pile = Pile::createFromString('As,Kd,2c');
        $drawn = $pile->drawMany(2);

        self::assertSame('2c,Kd', \implode(',', \array_map('strval', $drawn)));
        self::assertSame('As', (string) $pile);
        self::assertSame([], $pile->drawMany(0));
    }

    #[Test]
    public function cannotDrawMoreThanAvailable(): void
    {
        $pile = Pile::createFromString('As,Kd');

        $this->expectException(\UnderflowException::class);
        $this->expectExceptionMessage('Cannot draw 3 cards from a pile of 2.');
        $pile->drawMany(3);
    }

    #[Test]
    public function cannotDrawNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Pile::createFromString('As')->drawMany(-1);
    }

    #[Test]
    public function peek(): void
    {
        $pile = Pile::createFromString('2c,Kd,As');

        self::assertSame('As,Kd', \implode(',', \array_map('strval', $pile->peek(2))));
        self::assertCount(3, $pile->peek(5));
        self::assertSame([], $pile->peek(0));
        self::assertSame([], (new Pile())->peek(2));
        self::assertCount(3, $pile);
    }

    #[Test]
    public function cannotPeekNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Pile::createFromString('As')->peek(-1);
    }

    #[Test]
    public function shuffleIsReproducibleWithASeed(): void
    {
        $first = new Pile(Card::getDeck());
        $second = new Pile(Card::getDeck());
        $first->shuffle(new Randomizer(new Mt19937(42)));
        $second->shuffle(new Randomizer(new Mt19937(42)));

        self::assertSame((string) $first, (string) $second);
        self::assertNotSame((string) $first, (string) new Pile(Card::getDeck()));
    }

    #[Test]
    public function faceLookups(): void
    {
        $pile = Pile::createFromString('Asr,Asb,Kd');

        self::assertFalse($pile->has(Card::fromRankSuit('As')));
        self::assertTrue($pile->hasFace(Card::fromRankSuit('As')));
        self::assertSame(2, $pile->countFace(Card::fromRankSuit('As')));
    }

    #[Test]
    public function toCardsAndHiddenString(): void
    {
        $pile = Pile::createFromString('Asr,Kd');

        self::assertSame('Asr,Kd', $pile->toCards()->toString(true));
        self::assertSame('??r,??', $pile->toHiddenString());
    }
}
