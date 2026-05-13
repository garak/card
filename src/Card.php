<?php

namespace Garak\Card;

use Random\Randomizer;

final readonly class Card implements \Stringable
{
    public function __construct(private Rank $rank, private Suit $suit)
    {
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

        $deck = [];
        for ($i = 1; $i <= $num; ++$i) {
            foreach ($regularSuits as $suit) {
                foreach ($regularRanks as $rank) {
                    $deck[] = new self($rank, $suit);
                }
            }
        }
        if ($allowJokers) {
            $deck[] = new self(Rank::Joker, Suit::BlackJoker);
            $deck[] = new self(Rank::Joker, Suit::RedJoker);
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

    public function isEqual(self $card): bool
    {
        return $this->suit->isEqual($card->suit) && $this->rank->isEqual($card->rank);
    }
}
