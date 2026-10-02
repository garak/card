<?php

namespace Garak\Card;

/**
 * How much each card is worth in a game, for scoring (points, penalties, blackjack sums)
 * or as a numeric rank (see aceHigh() and aceLow()).
 * Values are given by rank, with optional overrides for single cards (e.g. the queen of spades in hearts),
 * and a default for the cards not listed.
 */
final readonly class CardValues
{
    /**
     * @param array<int|string, int> $byRank  keyed by rank value (e.g. ['A' => 11, 'K' => 4])
     * @param array<string, int>     $byCard  keyed by card face, rank and suit (e.g. ['Qs' => 13]), overriding the rank value
     * @param int|null               $default value of the cards not listed, null to refuse them
     */
    public function __construct(
        private array $byRank = [],
        private array $byCard = [],
        private ?int $default = null,
    ) {
    }

    /**
     * Values following a rank order: the lowest rank is worth $first, each next one $step more.
     */
    public static function fromRankOrder(RankOrder $order, int $first = 1, int $step = 1): self
    {
        $byRank = [];
        foreach ($order->getRanks() as $position => $rank) {
            $byRank[$rank->value] = $first + $position * $step;
        }

        return new self($byRank);
    }

    /**
     * Two is 2, ten is 10, jack 11, queen 12, king 13, ace 14.
     */
    public static function aceHigh(): self
    {
        return self::fromRankOrder(RankOrder::aceHigh(), 2);
    }

    /**
     * Ace is 1, two is 2, ten 10, jack 11, queen 12, king 13.
     */
    public static function aceLow(): self
    {
        return self::fromRankOrder(RankOrder::aceLow(), 1);
    }

    public function withRank(Rank $rank, int $value): self
    {
        return new self([...$this->byRank, $rank->value => $value], $this->byCard, $this->default);
    }

    /**
     * Sets the value of a single card (whatever its back), overriding the value of its rank.
     */
    public function withCard(Card $card, int $value): self
    {
        return new self($this->byRank, [...$this->byCard, $card->toString() => $value], $this->default);
    }

    public function withDefault(?int $default): self
    {
        return new self($this->byRank, $this->byCard, $default);
    }

    public function has(Card $card): bool
    {
        return isset($this->byCard[$card->toString()]) || $this->hasRank($card->getRank());
    }

    public function hasRank(Rank $rank): bool
    {
        return isset($this->byRank[$rank->value]) || null !== $this->default;
    }

    /**
     * @throws \InvalidArgumentException if the card has no value and there is no default
     */
    public function of(Card $card): int
    {
        return $this->byCard[$card->toString()]
            ?? $this->byRank[$card->getRank()->value]
            ?? $this->default
            ?? throw new \InvalidArgumentException(\sprintf('Card %s has no value.', $card));
    }

    /**
     * @throws \InvalidArgumentException if the rank has no value and there is no default
     */
    public function ofRank(Rank $rank): int
    {
        return $this->byRank[$rank->value]
            ?? $this->default
            ?? throw new \InvalidArgumentException(\sprintf('Rank %s has no value.', $rank->value));
    }

    /**
     * @param iterable<Card> $cards
     */
    public function sum(iterable $cards): int
    {
        $sum = 0;
        foreach ($cards as $card) {
            $sum += $this->of($card);
        }

        return $sum;
    }

    public function compare(Card $a, Card $b): int
    {
        return $this->of($a) <=> $this->of($b);
    }
}
