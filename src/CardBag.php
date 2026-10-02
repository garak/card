<?php

namespace Garak\Card;

/**
 * A multiset of cards: how many times each card is present, regardless of order.
 * Cards are told apart by rank, suit and back (see Card::isEqual()), so that identical faces
 * from different decks count separately.
 * Useful to check that a set of cards is contained in another one, or to find what changed between two layouts.
 */
final class CardBag implements \Countable
{
    /** @var array<string, int> */
    private array $counts = [];

    /** @var array<string, Card> one card for each key */
    private array $cards = [];

    /**
     * @param iterable<Card> $cards
     */
    public function __construct(iterable $cards = [])
    {
        foreach ($cards as $card) {
            $this->add($card);
        }
    }

    public function add(Card ...$cards): void
    {
        foreach ($cards as $card) {
            $key = $card->toString(true);
            $this->counts[$key] = ($this->counts[$key] ?? 0) + 1;
            $this->cards[$key] ??= $card;
        }
    }

    /**
     * Removes one occurrence of each given card. Nothing is removed if any of them is missing.
     *
     * @throws \InvalidArgumentException if a card is not in the bag (as many times as requested)
     */
    public function remove(Card ...$cards): void
    {
        $counts = $this->counts;
        foreach ($cards as $card) {
            $key = $card->toString(true);
            if (($counts[$key] ?? 0) < 1) {
                throw new \InvalidArgumentException(\sprintf('Card %s is not in the bag.', $key));
            }
            --$counts[$key];
        }
        $this->counts = \array_filter($counts, static fn (int $count): bool => $count > 0);
        $this->cards = \array_intersect_key($this->cards, $this->counts);
    }

    public function has(Card $card): bool
    {
        return isset($this->counts[$card->toString(true)]);
    }

    /**
     * How many times the card is in the bag.
     */
    public function countOf(Card $card): int
    {
        return $this->counts[$card->toString(true)] ?? 0;
    }

    /**
     * Cards in this bag that are not in the other one, with multiplicity.
     *
     * @return list<Card>
     */
    public function diff(self $other): array
    {
        $missing = [];
        foreach ($this->counts as $key => $count) {
            $extra = $count - ($other->counts[$key] ?? 0);
            for ($i = 0; $i < $extra; ++$i) {
                $missing[] = $this->cards[$key];
            }
        }

        return $missing;
    }

    public function isSubsetOf(self $other): bool
    {
        return [] === $this->diff($other);
    }

    public function equals(self $other): bool
    {
        return $this->isSubsetOf($other) && $other->isSubsetOf($this);
    }

    public function isEmpty(): bool
    {
        return [] === $this->counts;
    }

    /**
     * Total number of cards, with multiplicity.
     */
    public function count(): int
    {
        return \max(0, \array_sum($this->counts));
    }

    /**
     * All the cards, with multiplicity, grouped by card.
     *
     * @return list<Card>
     */
    public function toArray(): array
    {
        $cards = [];
        foreach ($this->counts as $key => $count) {
            for ($i = 0; $i < $count; ++$i) {
                $cards[] = $this->cards[$key];
            }
        }

        return $cards;
    }
}
