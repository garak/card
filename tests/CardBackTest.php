<?php

namespace Garak\Card\Test;

use Garak\Card\CardBack;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CardBackTest extends TestCase
{
    #[Test]
    public function toText(): void
    {
        self::assertEquals('r', CardBack::Red->toText());
        self::assertEquals('b', CardBack::Blue->toText());
    }

    #[Test]
    public function fromText(): void
    {
        self::assertEquals(CardBack::Red, CardBack::fromText('r'));
        self::assertEquals(CardBack::Blue, CardBack::fromText('b'));
    }

    #[Test]
    public function textRoundTrip(): void
    {
        foreach (CardBack::cases() as $back) {
            self::assertEquals($back, CardBack::fromText($back->toText()));
        }
    }

    #[Test]
    public function fromTextRejectsUnknownText(): void
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage('"x" is not a valid back text for enum Garak\Card\CardBack');
        CardBack::fromText('x');
    }

    #[Test]
    public function isEqual(): void
    {
        self::assertTrue(CardBack::Red->isEqual(CardBack::Red));
        self::assertFalse(CardBack::Red->isEqual(CardBack::Blue));
    }
}
