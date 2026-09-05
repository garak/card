<?php

namespace Garak\Card;

use Random\Randomizer;

final readonly class Card implements \Stringable
{
    public function __construct(
        private Rank $rank,
        private Suit $suit,
        private ?CardBack $back = null
    ) {
    }

    /**
     * Accepts a 2-char string (rank and suit, e.g. "As") or a 3-char string
     * with a trailing back (e.g. "Asr" for an ace of spades with red back).
     */
    public static function fromRankSuit(string $rankSuit): self
    {
        $length = \strlen($rankSuit);
        if ($length < 2 || $length > 3) {
            throw new \InvalidArgumentException(\sprintf('Invalid card string "%s": expected 2 or 3 characters.', $rankSuit));
        }
        $back = 3 === $length ? CardBack::fromText($rankSuit[2]) : null;

        return new self(Rank::from($rankSuit[0]), Suit::from($rankSuit[1]), $back);
    }

    /**
     * @return array|self[]
     */
    public static function getDeck(bool $shuffle = false, int $num = 1, bool $allowJokers = false): array
    {
        $regularSuits = [Suit::Clubs, Suit::Diamonds, Suit::Hearts, Suit::Spades];
        $regularRanks = \array_filter(Rank::cases(), static fn (Rank $r): bool => Rank::Joker !== $r);

        $backs = CardBack::cases();
        $deck = [];
        for ($i = 1; $i <= $num; ++$i) {
            $back = $num > 1 ? $backs[($i - 1) % \count($backs)] : null;
            foreach ($regularSuits as $suit) {
                foreach ($regularRanks as $rank) {
                    $deck[] = new self($rank, $suit, $back);
                }
            }
            if ($allowJokers) {
                $deck[] = new self(Rank::Joker, Suit::BlackJoker, $back);
                $deck[] = new self(Rank::Joker, Suit::RedJoker, $back);
            }
        }
        if ($shuffle) {
            return (new Randomizer())->shuffleArray($deck);
        }

        return $deck;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * String representation, parsable by fromRankSuit().
     * The back (if any) is included only when explicitly requested.
     */
    public function toString(bool $withBack = false): string
    {
        $string = $this->rank->value.$this->suit->value;
        if ($withBack && null !== $this->back) {
            $string .= $this->back->toText();
        }

        return $string;
    }

    public function toText(): string
    {
        return $this->rank->toText().$this->suit->toText();
    }

    public function toHtml(string $template = '<span id="%s" class="crd crd-%s st-%s">%s%s</span>'): string
    {
        return \sprintf($template, $this->rank->value.$this->suit->value, $this->rank->value, $this->suit->value, $this->rank->toText(), $this->suit->toText());
    }

    public function toUnicode(): string
    {
        return CardCode::from($this->rank->value.$this->suit->value)->unicode();
    }

    public function getSuit(): Suit
    {
        return $this->suit;
    }

    public function getRank(): Rank
    {
        return $this->rank;
    }

    public function getBack(): ?CardBack
    {
        return $this->back;
    }

    public function isSameFace(self $card): bool
    {
        return $this->suit->isEqual($card->suit) && $this->rank->isEqual($card->rank);
    }

    public function isEqual(self $card): bool
    {
        if (null === $this->back || null === $card->back) {
            return null === $this->back && null === $card->back && $this->isSameFace($card);
        }

        return $this->isSameFace($card) && $this->back->isEqual($card->back);
    }
}
