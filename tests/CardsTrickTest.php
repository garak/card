<?php

namespace Garak\Card\Test;

use Garak\Card\Card;
use Garak\Card\Suit;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CardsTrickTest extends TestCase
{
    #[Test]
    public function constructor(): void
    {
        $trick = new CardsTrickStub([Card::fromRankSuit('4d')]);
        self::assertEquals('4d', (string) $trick->getWinningCard(null));
        self::assertCount(1, $trick->getCards());
    }

    #[Test]
    public function winningKeyOfAnOverrideReturningANewInstance(): void
    {
        $trick = new CardsTrickStub(['a' => Card::fromRankSuit('2c'), 'b' => Card::fromRankSuit('4d')]);

        self::assertSame('b', $trick->getWinningKey(null));
    }

    #[Test]
    public function winningKeyOfACardNotInTheTrick(): void
    {
        $trick = new CardsTrickStub(['a' => Card::fromRankSuit('2c')]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The winning card 4d is not in the trick.');
        $trick->getWinningKey(null);
    }

    #[Test]
    public function ledSuit(): void
    {
        self::assertNull((new TrickStub([]))->getLedSuit());
        self::assertSame(Suit::Hearts, self::trick('N:5h,E:Kh')->getLedSuit());
    }

    #[Test]
    public function highestOfLedSuitWinsWithoutTrump(): void
    {
        $trick = self::trick('N:5h,E:Kh,S:As,W:2h');

        self::assertSame('Kh', (string) $trick->getWinningCard(null));
        self::assertSame('E', $trick->getWinningKey(null));
    }

    #[Test]
    public function highestTrumpWins(): void
    {
        $trick = self::trick('N:5h,E:Kh,S:2s,W:As');

        self::assertSame('As', (string) $trick->getWinningCard(Suit::Spades));
        self::assertSame('W', $trick->getWinningKey(Suit::Spades));
    }

    #[Test]
    public function trumpNotPlayedIsIgnored(): void
    {
        $trick = self::trick('N:5h,E:Kh,S:As,W:2h');

        self::assertSame('Kh', (string) $trick->getWinningCard(Suit::Clubs));
    }

    #[Test]
    public function offSuitCardsNeverWin(): void
    {
        $trick = self::trick('N:2h,E:Ac,S:Ad,W:As');

        self::assertSame('2h', (string) $trick->getWinningCard(null));
        self::assertSame('N', $trick->getWinningKey(null));
    }

    #[Test]
    public function tiesGoToTheFirstCard(): void
    {
        $trick = new TrickStub(['a' => Card::fromRankSuit('Khr'), 'b' => Card::fromRankSuit('Khb')]);

        self::assertSame('Khr', $trick->getWinningCard(null)->toString(true));
        self::assertSame('a', $trick->getWinningKey(null));
    }

    #[Test]
    public function numericKeys(): void
    {
        $trick = new TrickStub([Card::fromRankSuit('2h'), Card::fromRankSuit('Th')]);

        self::assertSame(1, $trick->getWinningKey(null));
    }

    #[Test]
    public function customRankOrder(): void
    {
        $trick = new AceLowTrickStub(['a' => Card::fromRankSuit('Ah'), 'b' => Card::fromRankSuit('2h')]);

        self::assertSame('2h', (string) $trick->getWinningCard(null));
    }

    #[Test]
    public function emptyTrickHasNoWinner(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('No cards in trick.');
        (new TrickStub([]))->getWinningCard(null);
    }

    #[Test]
    public function mustFollowSuit(): void
    {
        $trick = self::trick('N:5h');
        $hand = HandStub::createFromString('2h,As', false);

        self::assertTrue($trick->canPlay(Card::fromRankSuit('2h'), $hand));
        self::assertFalse($trick->canPlay(Card::fromRankSuit('As'), $hand));
    }

    #[Test]
    public function canDiscardWithoutTheLedSuit(): void
    {
        $trick = self::trick('N:5h');
        $hand = HandStub::createFromString('Kc,As', false);

        self::assertTrue($trick->canPlay(Card::fromRankSuit('As'), $hand));
    }

    #[Test]
    public function canPlayAnythingInAnEmptyTrick(): void
    {
        $hand = HandStub::createFromString('Kc,As', false);

        self::assertTrue((new TrickStub([]))->canPlay(Card::fromRankSuit('Kc'), $hand));
    }

    #[Test]
    public function cannotPlayACardNotInHand(): void
    {
        $hand = HandStub::createFromString('Kc,As', false);

        self::assertFalse((new TrickStub([]))->canPlay(Card::fromRankSuit('2c'), $hand));
        self::assertFalse(self::trick('N:5h')->canPlay(Card::fromRankSuit('5h'), $hand));
    }

    /**
     * @param string $cards e.g. "N:5h,E:Kh"
     */
    private static function trick(string $cards): TrickStub
    {
        $played = [];
        foreach (\explode(',', $cards) as $keyed) {
            [$key, $card] = \explode(':', $keyed);
            $played[$key] = Card::fromRankSuit($card);
        }

        return new TrickStub($played);
    }
}
