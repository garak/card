<?php

namespace Garak\Card;

/**
 * An immutable, ordered list of cards, with no game meaning attached: the cards on the table,
 * the community cards, a capture, or anything that is neither a Hand nor a Pile.
 * Every method returning cards returns a new instance.
 *
 * @implements \IteratorAggregate<int, Card>
 */
final readonly class Cards implements \Countable, \IteratorAggregate, \Stringable
{
    /** @var list<Card> */
    private array $cards;

    /**
     * @param iterable<Card> $cards
     */
    public function __construct(iterable $cards = [])
    {
        $this->cards = \is_array($cards) ? \array_values($cards) : \iterator_to_array($cards, false);
    }

    /**
     * @param string $cards comma-separated cards (e.g. "2c,Kd,Asr"), or an empty string
     */
    public static function fromString(string $cards): self
    {
        if ('' === $cards) {
            return new self();
        }

        return new self(\array_map(static fn (string $rs): Card => Card::fromRankSuit($rs), \explode(',', $cards)));
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * String representation, parsable by fromString().
     * Backs (if any) are included only when explicitly requested.
     */
    public function toString(bool $withBack = false): string
    {
        return \implode(',', \array_map(static fn (Card $card): string => $card->toString($withBack), $this->cards));
    }

    /**
     * Representation of the cards as seen from their backs, see Card::toHiddenString().
     */
    public function toHiddenString(): string
    {
        return \implode(',', \array_map(static fn (Card $card): string => $card->toHiddenString(), $this->cards));
    }

    /**
     * @return list<Card>
     */
    public function toArray(): array
    {
        return $this->cards;
    }

    public function toBag(): CardBag
    {
        return new CardBag($this->cards);
    }

    /**
     * @return \ArrayIterator<int, Card>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->cards);
    }

    public function count(): int
    {
        return \count($this->cards);
    }

    public function isEmpty(): bool
    {
        return [] === $this->cards;
    }

    public function first(): ?Card
    {
        return $this->cards[0] ?? null;
    }

    public function last(): ?Card
    {
        return $this->cards[\count($this->cards) - 1] ?? null;
    }

    public function has(Card $card): bool
    {
        return \array_any($this->cards, static fn (Card $c): bool => $card->isEqual($c));
    }

    /**
     * Whether a card with the same rank and suit is present, whatever its back.
     */
    public function hasFace(Card $card): bool
    {
        return \array_any($this->cards, static fn (Card $c): bool => $card->isSameFace($c));
    }

    /**
     * How many cards with the same rank and suit are present, whatever their backs.
     */
    public function countFace(Card $card): int
    {
        return \count(\array_filter($this->cards, static fn (Card $c): bool => $card->isSameFace($c)));
    }

    public function hasSuit(Suit $suit): bool
    {
        return \array_any($this->cards, static fn (Card $c): bool => $c->getSuit()->isEqual($suit));
    }

    public function hasRank(Rank $rank): bool
    {
        return \array_any($this->cards, static fn (Card $c): bool => $c->getRank()->isEqual($rank));
    }

    public function with(Card ...$cards): self
    {
        return new self([...$this->cards, ...$cards]);
    }

    /**
     * Removes one occurrence of each given card.
     *
     * @throws \InvalidArgumentException if a card is not present
     */
    public function without(Card ...$cards): self
    {
        $remaining = $this->cards;
        foreach ($cards as $card) {
            $key = \array_find_key($remaining, static fn (Card $c): bool => $card->isEqual($c))
                ?? throw new \InvalidArgumentException(\sprintf('Card %s not present in %s.', $card, $this));
            unset($remaining[$key]);
        }

        return new self($remaining);
    }

    /**
     * @param callable(Card): bool $callback
     */
    public function filter(callable $callback): self
    {
        return new self(\array_filter($this->cards, $callback));
    }

    public function ofSuit(Suit $suit): self
    {
        return $this->filter(static fn (Card $c): bool => $c->getSuit()->isEqual($suit));
    }

    public function ofRank(Rank $rank): self
    {
        return $this->filter(static fn (Card $c): bool => $c->getRank()->isEqual($rank));
    }

    /**
     * The cards that are not jokers.
     */
    public function regular(): self
    {
        return $this->filter(static fn (Card $c): bool => !$c->isJoker());
    }

    public function jokers(): self
    {
        return $this->filter(static fn (Card $c): bool => $c->isJoker());
    }

    /**
     * The cards split by suit, in order of first appearance.
     *
     * @return array<string, self> keyed by suit value
     */
    public function groupBySuit(): array
    {
        return $this->groupBy(static fn (Card $c): string => $c->getSuit()->value);
    }

    /**
     * The cards split by rank, in order of first appearance.
     * Beware that PHP turns numeric keys into integers: use $rank->value as the key, not a string literal.
     *
     * @return array<int|string, self> keyed by rank value
     */
    public function groupByRank(): array
    {
        return $this->groupBy(static fn (Card $c): string => $c->getRank()->value);
    }

    /**
     * @param callable(Card, Card): int $comparator e.g. a CardComparator
     */
    public function sort(callable $comparator): self
    {
        $cards = $this->cards;
        \usort($cards, $comparator);

        return new self($cards);
    }

    public function sum(CardValues $values): int
    {
        return $values->sum($this->cards);
    }

    public function merge(self $other): self
    {
        return new self([...$this->cards, ...$other->cards]);
    }

    /**
     * @template TKey of array-key
     *
     * @param callable(Card): TKey $key
     *
     * @return array<TKey, self>
     */
    private function groupBy(callable $key): array
    {
        $groups = [];
        foreach ($this->cards as $card) {
            $groups[$key($card)][] = $card;
        }

        return \array_map(static fn (array $cards): self => new self($cards), $groups);
    }
}
