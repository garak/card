<?php

namespace Garak\Card;

use Random\Randomizer;

/**
 * An ordered stack of cards, like a stock, a discard pile, a talon, or a dealing shoe.
 * The last card in the internal list is the top of the pile.
 * Unlike Hand, a Pile is mutable: drawing and adding change its state in place.
 */
class Pile implements \Countable, \Stringable
{
    /** @var list<Card> */
    private array $cards;

    /**
     * @param array<int|string, Card> $cards ordered from bottom to top
     */
    final public function __construct(array $cards = [])
    {
        $this->cards = \array_values($cards);
    }

    /**
     * Builds a pile from cards ordered from top to bottom: the first given card is the first to be drawn.
     * Handy for a deck to be dealt in order, see also Deck::createPile().
     *
     * @param array<int|string, Card> $cards ordered from top to bottom
     */
    public static function createFromTop(array $cards): static
    {
        return new static(\array_reverse(\array_values($cards)));
    }

    /**
     * @param string $cards comma-separated cards, ordered from bottom to top (e.g. "2c,Kd,Asr")
     */
    public static function createFromString(string $cards): static
    {
        return new static(Cards::fromString($cards)->toArray());
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * String representation, parsable by createFromString().
     * Backs (if any) are included only when explicitly requested.
     */
    public function toString(bool $withBack = false): string
    {
        return \implode(',', \array_map(static fn (Card $card): string => $card->toString($withBack), $this->cards));
    }

    /**
     * Representation of the pile as seen from the backs of its cards, e.g. a face-down stock.
     * See Card::toHiddenString().
     */
    public function toHiddenString(): string
    {
        return $this->toCards()->toHiddenString();
    }

    /**
     * Puts one or more cards on top of the pile, in the given order (the last one ends on top).
     */
    public function add(Card ...$cards): void
    {
        foreach ($cards as $card) {
            $this->cards[] = $card;
        }
    }

    /**
     * Removes and returns the top card.
     *
     * @throws \UnderflowException if the pile is empty
     */
    public function draw(): Card
    {
        return \array_pop($this->cards) ?? throw new \UnderflowException('Cannot draw from an empty pile.');
    }

    /**
     * Removes and returns the given number of cards from the top, the top one first.
     *
     * @return list<Card>
     *
     * @throws \UnderflowException if the pile has not enough cards
     */
    public function drawMany(int $count): array
    {
        if ($count < 0) {
            throw new \InvalidArgumentException('Cannot draw a negative number of cards.');
        }
        if ($count > \count($this->cards)) {
            throw new \UnderflowException(\sprintf('Cannot draw %d cards from a pile of %d.', $count, \count($this->cards)));
        }
        $drawn = [];
        for ($i = 0; $i < $count; ++$i) {
            $drawn[] = \array_pop($this->cards) ?? throw new \LogicException('Unexpected empty pile.');
        }

        return $drawn;
    }

    /**
     * Returns the top card without removing it, or null if the pile is empty.
     */
    public function top(): ?Card
    {
        return $this->cards[\count($this->cards) - 1] ?? null;
    }

    /**
     * Returns up to the given number of cards from the top, the top one first, without removing them.
     *
     * @return list<Card>
     */
    public function peek(int $count): array
    {
        if ($count < 0) {
            throw new \InvalidArgumentException('Cannot peek a negative number of cards.');
        }
        if (0 === $count) {
            return [];
        }

        return \array_reverse(\array_slice($this->cards, -$count));
    }

    /**
     * Removes and returns all cards, ordered from bottom to top. The pile is left empty.
     *
     * @return list<Card>
     */
    public function takeAll(): array
    {
        $cards = $this->cards;
        $this->cards = [];

        return $cards;
    }

    /**
     * @param Randomizer|null $randomizer pass a seeded one for a reproducible order
     */
    public function shuffle(?Randomizer $randomizer = null): void
    {
        $this->cards = \array_values(($randomizer ?? new Randomizer())->shuffleArray($this->cards));
    }

    public function has(Card $card): bool
    {
        return \array_any($this->cards, static fn (Card $c): bool => $card->isEqual($c));
    }

    /**
     * Whether a card with the same rank and suit is in the pile, whatever its back.
     */
    public function hasFace(Card $card): bool
    {
        return \array_any($this->cards, static fn (Card $c): bool => $card->isSameFace($c));
    }

    /**
     * How many cards with the same rank and suit are in the pile, whatever their backs.
     */
    public function countFace(Card $card): int
    {
        return \count(\array_filter($this->cards, static fn (Card $c): bool => $card->isSameFace($c)));
    }

    public function isEmpty(): bool
    {
        return [] === $this->cards;
    }

    public function count(): int
    {
        return \count($this->cards);
    }

    /**
     * @return list<Card> ordered from bottom to top
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    /**
     * The cards of the pile as a Cards list, ordered from bottom to top.
     */
    public function toCards(): Cards
    {
        return new Cards($this->cards);
    }
}
