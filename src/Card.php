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

    public static function fromRankSuit(string $rankSuit): self
    {
        [$value, $suit] = \str_split($rankSuit);

        return new self(Rank::from($value), Suit::from($suit));
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
        return $this->rank->value.$this->suit->value;
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
