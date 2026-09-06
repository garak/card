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
     * @param string $cards comma-separated cards, ordered from bottom to top (e.g. "2c,Kd,Asr")
     */
    public static function createFromString(string $cards): static
    {
        if ('' === $cards) {
            return new static();
        }

        return new static(\array_map(static fn (string $rs): Card => Card::fromRankSuit($rs), \explode(',', $cards)));
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
     * Returns the top card without removing it, or null if the pile is empty.
     */
    public function top(): ?Card
    {
        return $this->cards[\count($this->cards) - 1] ?? null;
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

    public function shuffle(): void
    {
        $this->cards = \array_values((new Randomizer())->shuffleArray($this->cards));
    }

    public function has(Card $card): bool
    {
        return \array_any($this->cards, static fn (Card $c): bool => $card->isEqual($c));
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
}
