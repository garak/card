<?php

namespace Garak\Card;

/**
 * The order of the suits in a game, from the lowest to the highest.
 * Bridge ranks them clubs, diamonds, hearts, spades; other games differ, or only use an order to sort a hand.
 */
final readonly class SuitOrder implements \Countable
{
    /** @var list<Suit> */
    private array $suits;

    /** @var array<string, int> position of each suit, keyed by suit value */
    private array $positions;

    /**
     * @param list<Suit> $suits from the lowest to the highest
     */
    public function __construct(array $suits)
    {
        if ([] === $suits) {
            throw new \InvalidArgumentException('A suit order needs at least one suit.');
        }
        $positions = [];
        foreach ($suits as $position => $suit) {
            if (isset($positions[$suit->value])) {
                throw new \InvalidArgumentException(\sprintf('Suit %s appears twice in the order.', $suit->value));
            }
            $positions[$suit->value] = $position;
        }
        $this->suits = $suits;
        $this->positions = $positions;
    }

    /**
     * Clubs, diamonds, hearts, spades: the bridge order.
     */
    public static function standard(): self
    {
        return new self(Suit::regular());
    }

    /**
     * @return list<Suit>
     */
    public function getSuits(): array
    {
        return $this->suits;
    }

    public function has(Suit $suit): bool
    {
        return isset($this->positions[$suit->value]);
    }

    /**
     * Position of the suit in the order, starting from 0 for the lowest.
     *
     * @throws \InvalidArgumentException if the suit is not in the order
     */
    public function indexOf(Suit $suit): int
    {
        return $this->positions[$suit->value]
            ?? throw new \InvalidArgumentException(\sprintf('Suit %s is not in the order.', $suit->value));
    }

    public function compare(Suit $a, Suit $b): int
    {
        return $this->indexOf($a) <=> $this->indexOf($b);
    }

    public function compareCards(Card $a, Card $b): int
    {
        return $this->compare($a->getSuit(), $b->getSuit());
    }

    public function count(): int
    {
        return \count($this->suits);
    }
}
