<?php

namespace Garak\Card;

/**
 * A Hand is composed by Cards.
 * You must extend this class, and pass to constructor your anonymous functions to check starting hand
 * and to sort it.
 */
abstract class Hand
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
    abstract public function __construct(array $cards, bool $start = true, ?callable $checking = null, ?callable $sorting = null);

    /**
     * @return array<int, static>
     */
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

    public static function createFromString(string $cards, bool $starting = true, ?callable $check = null, ?callable $sort = null): self
    {
        $handCards = \array_map(static fn (string $rs): Card => Card::fromRankSuit($rs), \explode(',', $cards));

        return new static($handCards, $starting, $check, $sort);
    }

    public function __toString(): string
    {
        return \implode(',', $this->cards);
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

    public static function isValid(string $cards): bool
    {
        return 2 === \strlen($cards) || \strpos($cards, ',') > 0;
    }

    public function play(Card $card, ?callable $sort = null): self
    {
        $played = null;
        foreach ($this->cards as $key => $cardInHand) {
            if ($card->isEqual($cardInHand)) {
                $played = $key;
                break;
            }
        }
        if (null === $played) {
            throw new \InvalidArgumentException(\sprintf('Card %s not present in hand (%s).', $card, $this));
        }
        $cards = $this->cards;
        unset($cards[$played]);

        return new static($cards, false, null, $sort);
    }

    public function isEmpty(): bool
    {
        return 0 === \count($this->cards);
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
