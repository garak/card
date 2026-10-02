<?php

namespace Garak\Card;

/**
 * A Hand is composed by Cards.
 * You must extend this class, and pass to constructor your anonymous functions to check starting
 * hand and to sort it.
 */
abstract class Hand implements \Countable, \Stringable
{
    /** @var array<int|string, Card> */
    protected array $cards;

    /** @var callable */
    protected $sorting;

    protected bool $sorted = false;

    protected ?Suit $sortedSuit = null;

    /**
     * You should perform checking here.
     *
     * @param array<int|string, Card> $cards
     */
    abstract public function __construct(
        array $cards,
        bool $start = true,
        ?callable $checking = null,
        ?callable $sorting = null
    );

    /**
     * @deprecated since 0.13, to be removed in 1.0: it only deals 13 cards of a single deck to 4 hands.
     *             Use a Deck, then Pile::drawMany() for each hand.
     *
     * @return array<int, static>
     */
    #[\Deprecated('Use a Deck, then Pile::drawMany() for each hand')]
    public static function deal(?callable $check = null, ?callable $sort = null): array
    {
        $deck = Card::getDeck(true);
        $cards = \array_chunk($deck, 13);
        $hands = [];
        foreach ($cards as $handCards) {
            $hands[] = new static($handCards, true, $check, $sort);
        }

        return $hands;
    }

    public static function createFromString(string $cards, bool $starting = true, ?callable $check = null, ?callable $sort = null): static
    {
        return new static(Cards::fromString($cards)->toArray(), $starting, $check, $sort);
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
     * Representation of the hand as seen from the backs of its cards, e.g. an opponent's hand.
     * See Card::toHiddenString().
     */
    public function toHiddenString(): string
    {
        return $this->toCards()->toHiddenString();
    }

    public function toText(?Suit $trump = null): string
    {
        $this->sort($trump);

        return \implode(' ', \array_map(static fn (Card $card): string => $card->toText(), $this->cards));
    }

    public function toHtml(?Suit $trump = null): string
    {
        $this->sort($trump);

        return \implode('', \array_map(static fn (Card $card): string => $card->toHtml(), $this->cards));
    }

    /**
     * @return array<Card>
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    /**
     * The cards of the hand as a Cards list, for filtering, grouping and the like.
     */
    public function toCards(): Cards
    {
        return new Cards($this->cards);
    }

    public function getRandomCard(?Suit $suit = null): Card
    {
        $count = \count($this->cards);
        if ($count < 1) {
            throw new \DomainException('No cards left.');
        }
        if (null !== $suit) {
            $cards = \array_filter($this->cards, static fn (Card $card): bool => $card->getSuit()->isEqual($suit));
            if (\count($cards) > 0) {
                $key = \array_rand($cards);

                return $cards[$key];
            }
        }
        $key = \array_rand($this->cards);

        return $this->cards[$key];
    }

    /**
     * @deprecated since 0.13, to be removed in 1.0: it only checks the shape of the string, use Cards::fromString() and catch its exceptions
     */
    #[\Deprecated('Use Cards::fromString() and catch its exceptions')]
    public static function isValid(string $cards): bool
    {
        return \in_array(\strlen($cards), [2, 3], true) || \strpos($cards, ',') > 0;
    }

    public function has(Card $card): bool
    {
        return \array_any($this->cards, static fn (Card $c): bool => $card->isEqual($c));
    }

    /**
     * Whether a card with the same rank and suit is in hand, whatever its back.
     */
    public function hasFace(Card $card): bool
    {
        return \array_any($this->cards, static fn (Card $c): bool => $card->isSameFace($c));
    }

    /**
     * How many cards with the same rank and suit are in hand, whatever their backs.
     */
    public function countFace(Card $card): int
    {
        return \count(\array_filter($this->cards, static fn (Card $c): bool => $card->isSameFace($c)));
    }

    public function hasSuit(Suit $suit): bool
    {
        return \array_any($this->cards, static fn (Card $c): bool => $c->getSuit()->isEqual($suit));
    }

    /**
     * Returns a new hand with the given card added.
     */
    public function add(Card $card, ?callable $sort = null): static
    {
        return $this->addMany([$card], $sort);
    }

    /**
     * Returns a new hand with the given cards added.
     *
     * @param iterable<Card> $cards
     */
    public function addMany(iterable $cards, ?callable $sort = null): static
    {
        $handCards = $this->cards;
        foreach ($cards as $card) {
            $handCards[] = $card;
        }

        return new static($handCards, false, null, $sort);
    }

    /**
     * Returns a new hand without the given card.
     *
     * @throws \InvalidArgumentException if the card is not in hand
     */
    public function play(Card $card, ?callable $sort = null): static
    {
        return $this->playMany([$card], $sort);
    }

    /**
     * Returns a new hand without the given cards.
     *
     * @param iterable<Card> $cards
     *
     * @throws \InvalidArgumentException if a card is not in hand
     */
    public function playMany(iterable $cards, ?callable $sort = null): static
    {
        $handCards = $this->cards;
        foreach ($cards as $card) {
            $played = \array_find_key($handCards, static fn (Card $c): bool => $card->isEqual($c))
                ?? throw new \InvalidArgumentException(\sprintf('Card %s not present in hand (%s).', $card, $this));
            unset($handCards[$played]);
        }

        return new static($handCards, false, null, $sort);
    }

    public function isEmpty(): bool
    {
        return 0 === $this->count();
    }

    public function count(): int
    {
        return \count($this->cards);
    }

    public function sort(?Suit $trump): void
    {
        if ($this->isSuitSorted($trump)) {
            return;
        }
        if (null !== $this->sorting) {
            \call_user_func($this->sorting, $trump);
            $this->sorted = true;
            $this->sortedSuit = $trump;
        }
    }

    private function isSuitSorted(?Suit $suit): bool
    {
        if (null === $suit) {
            return $this->sorted && null === $this->sortedSuit;
        }

        return $this->sorted && null !== $this->sortedSuit && $suit->isEqual($this->sortedSuit);
    }
}
