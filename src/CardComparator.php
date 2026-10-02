<?php

namespace Garak\Card;

/**
 * Compares two cards for sorting, according to a rank order, a suit order, an optional trump, and a direction.
 * Cards whose rank is not in the rank order (typically jokers) always go last, whatever the direction,
 * and are not ordered among themselves.
 *
 * Usable wherever a callable is expected, e.g. in usort().
 */
final readonly class CardComparator
{
    private RankOrder $rankOrder;

    private SuitOrder $suitOrder;

    /**
     * @param RankOrder|null $rankOrder  defaults to ace high
     * @param SuitOrder|null $suitOrder  defaults to the standard order
     * @param bool           $suitFirst  whether to group by suit before comparing ranks (false: by rank, then by suit)
     * @param Suit|null      $trump      a suit ranking above every other one
     * @param bool           $descending whether the highest card comes first
     */
    public function __construct(
        ?RankOrder $rankOrder = null,
        ?SuitOrder $suitOrder = null,
        private bool $suitFirst = true,
        private ?Suit $trump = null,
        private bool $descending = false,
    ) {
        $this->rankOrder = $rankOrder ?? RankOrder::aceHigh();
        $this->suitOrder = $suitOrder ?? SuitOrder::standard();
    }

    public function withRankOrder(RankOrder $rankOrder): self
    {
        return new self($rankOrder, $this->suitOrder, $this->suitFirst, $this->trump, $this->descending);
    }

    public function withSuitOrder(SuitOrder $suitOrder): self
    {
        return new self($this->rankOrder, $suitOrder, $this->suitFirst, $this->trump, $this->descending);
    }

    public function withSuitFirst(bool $suitFirst = true): self
    {
        return new self($this->rankOrder, $this->suitOrder, $suitFirst, $this->trump, $this->descending);
    }

    public function withTrump(?Suit $trump): self
    {
        return new self($this->rankOrder, $this->suitOrder, $this->suitFirst, $trump, $this->descending);
    }

    public function withDescending(bool $descending = true): self
    {
        return new self($this->rankOrder, $this->suitOrder, $this->suitFirst, $this->trump, $descending);
    }

    public function __invoke(Card $a, Card $b): int
    {
        return $this->compare($a, $b);
    }

    public function compare(Card $a, Card $b): int
    {
        $aOrdered = $this->rankOrder->has($a->getRank());
        $bOrdered = $this->rankOrder->has($b->getRank());
        if ($aOrdered !== $bOrdered) {
            return $aOrdered ? -1 : 1;
        }
        if (!$aOrdered) {
            return 0;
        }
        $result = $this->compareOrdered($a, $b);

        return $this->descending ? -$result : $result;
    }

    /**
     * Returns a sorted copy of the given cards.
     *
     * @param array<int|string, Card> $cards
     *
     * @return list<Card>
     */
    public function sort(array $cards): array
    {
        $cards = \array_values($cards);
        \usort($cards, $this->compare(...));

        return $cards;
    }

    private function compareOrdered(Card $a, Card $b): int
    {
        $bySuit = $this->compareSuits($a->getSuit(), $b->getSuit());
        $byRank = $this->rankOrder->compareCards($a, $b);

        if ($this->suitFirst) {
            return 0 !== $bySuit ? $bySuit : $byRank;
        }

        return 0 !== $byRank ? $byRank : $bySuit;
    }

    private function compareSuits(Suit $a, Suit $b): int
    {
        if (null !== $this->trump && ($a === $this->trump || $b === $this->trump)) {
            return ($a === $this->trump) <=> ($b === $this->trump);
        }
        if (!$this->suitOrder->has($a) || !$this->suitOrder->has($b)) {
            return 0;
        }

        return $this->suitOrder->compare($a, $b);
    }
}
