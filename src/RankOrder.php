<?php

namespace Garak\Card;

/**
 * The order of the ranks in a game, from the lowest to the highest.
 * Games disagree on it: the ace is high in poker and bridge, low in rummy, the ten beats the king in pinochle.
 * Ranks left out of the order (typically the joker) cannot be compared: see CardComparator for how they are sorted.
 */
final readonly class RankOrder implements \Countable
{
    /** @var list<Rank> */
    private array $ranks;

    /** @var array<int|string, int> position of each rank, keyed by rank value */
    private array $positions;

    /**
     * @param list<Rank> $ranks from the lowest to the highest
     */
    public function __construct(array $ranks)
    {
        if ([] === $ranks) {
            throw new \InvalidArgumentException('A rank order needs at least one rank.');
        }
        $positions = [];
        foreach ($ranks as $position => $rank) {
            if (isset($positions[$rank->value])) {
                throw new \InvalidArgumentException(\sprintf('Rank %s appears twice in the order.', $rank->value));
            }
            $positions[$rank->value] = $position;
        }
        $this->ranks = $ranks;
        $this->positions = $positions;
    }

    /**
     * Two to ace, as in poker and bridge.
     */
    public static function aceHigh(): self
    {
        return new self(Rank::regular());
    }

    /**
     * Ace to king, as in rummy.
     */
    public static function aceLow(): self
    {
        return new self([Rank::Ace, ...\array_slice(Rank::regular(), 0, -1)]);
    }

    /**
     * @return list<Rank>
     */
    public function getRanks(): array
    {
        return $this->ranks;
    }

    public function has(Rank $rank): bool
    {
        return isset($this->positions[$rank->value]);
    }

    /**
     * Position of the rank in the order, starting from 0 for the lowest.
     *
     * @throws \InvalidArgumentException if the rank is not in the order
     */
    public function indexOf(Rank $rank): int
    {
        return $this->positions[$rank->value]
            ?? throw new \InvalidArgumentException(\sprintf('Rank %s is not in the order.', $rank->value));
    }

    public function getLowest(): Rank
    {
        return $this->ranks[0];
    }

    public function getHighest(): Rank
    {
        return $this->ranks[\count($this->ranks) - 1];
    }

    /**
     * The rank right above the given one, or null for the highest.
     */
    public function next(Rank $rank): ?Rank
    {
        return $this->ranks[$this->indexOf($rank) + 1] ?? null;
    }

    /**
     * The rank right below the given one, or null for the lowest.
     */
    public function previous(Rank $rank): ?Rank
    {
        $index = $this->indexOf($rank) - 1;

        return $index < 0 ? null : $this->ranks[$index];
    }

    public function compare(Rank $a, Rank $b): int
    {
        return $this->indexOf($a) <=> $this->indexOf($b);
    }

    public function compareCards(Card $a, Card $b): int
    {
        return $this->compare($a->getRank(), $b->getRank());
    }

    /**
     * The highest card among the given ones (the first one on ties), or null when there are none.
     *
     * @param iterable<Card> $cards
     */
    public function highest(iterable $cards): ?Card
    {
        $highest = null;
        foreach ($cards as $card) {
            if (null === $highest || $this->compareCards($card, $highest) > 0) {
                $highest = $card;
            }
        }

        return $highest;
    }

    /**
     * The lowest card among the given ones (the first one on ties), or null when there are none.
     *
     * @param iterable<Card> $cards
     */
    public function lowest(iterable $cards): ?Card
    {
        $lowest = null;
        foreach ($cards as $card) {
            if (null === $lowest || $this->compareCards($card, $lowest) < 0) {
                $lowest = $card;
            }
        }

        return $lowest;
    }

    public function count(): int
    {
        return \count($this->ranks);
    }
}
