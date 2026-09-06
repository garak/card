<?php

namespace Garak\Card\Test;

use Garak\Card\Suit;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SuitTest extends TestCase
{
    #[Test]
    public function getInt(): void
    {
        self::assertEquals(2, Suit::Diamonds->getInt());
    }

    #[Test]
    #[Group('legacy')]
    public function getName(): void
    {
        self::assertSame('d', Suit::Diamonds->getName());
    }

    #[Test]
    public function toText(): void
    {
        self::assertSame('♣', Suit::Clubs->toText());
        self::assertSame('♦', Suit::Diamonds->toText());
        self::assertSame('♥', Suit::Hearts->toText());
        self::assertSame('♠', Suit::Spades->toText());
    }

    #[Test]
    public function toTextForJokersUsesValue(): void
    {
        self::assertSame('b', Suit::BlackJoker->toText());
        self::assertSame('r', Suit::RedJoker->toText());
    }

    #[Test]
    public function getSymbolForJokersIsNotSupported(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Suit r has no symbol.');
        Suit::RedJoker->getSymbol();
    }

    #[Test]
    public function toUnicode(): void
    {
        self::assertEquals('♦️', Suit::Diamonds->toUnicode());
    }

    #[Test]
    public function toUnicodeForJokersIsNotSupported(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Suit b has no unicode representation.');
        Suit::BlackJoker->toUnicode();
    }

    #[Test]
    public function getIntForJokers(): void
    {
        self::assertSame(-1, Suit::BlackJoker->getInt());
        self::assertSame(-1, Suit::RedJoker->getInt());
    }
}
