<?php

namespace Garak\Card;

use Random\Randomizer;

/**
 * The composition of the cards used in a game: which ranks and suits, how many copies of them
 * (e.g. two decks for canasta, six for a blackjack shoe), how many jokers, and which backs.
 * Cards come out in a fixed order: copy by copy, suit by suit, rank by rank, each copy followed by its jokers.
 */
final readonly class Deck implements \Countable
{
    /** @var list<Rank> */
    public array $ranks;

    /** @var list<Suit> */
    public array $suits;

    /** @var list<CardBack> */
    public array $backs;

    /**
     * @param int                 $copies how many copies of the deck (e.g. 2 for a double deck)
     * @param int                 $jokers total number of jokers, black and red in turn, spread over the copies
     * @param list<Rank>|null     $ranks  the regular ranks to include, null for all of them
     *                                    (e.g. seven to ace for a 32-card deck)
     * @param list<Suit>|null     $suits  the regular suits to include, null for all of them
     * @param list<CardBack>|null $backs  the backs given to the copies in turn, cycling when there are more copies than backs;
     *                                    null for no back with a single copy and all the backs otherwise;
     *                                    an empty list for no backs at all (e.g. a shoe of identical decks)
     */
    public function __construct(
        public int $copies = 1,
        public int $jokers = 0,
        ?array $ranks = null,
        ?array $suits = null,
        ?array $backs = null,
    ) {
        if ($copies < 1) {
            throw new \InvalidArgumentException('A deck needs at least one copy.');
        }
        if ($jokers < 0) {
            throw new \InvalidArgumentException('Jokers cannot be negative.');
        }
        $ranks ??= Rank::regular();
        $suits ??= Suit::regular();
        if ([] === $ranks || [] === $suits) {
            throw new \InvalidArgumentException('A deck needs at least one rank and one suit.');
        }
        if (\array_any($ranks, static fn (Rank $rank): bool => $rank->isJoker())) {
            throw new \InvalidArgumentException('Jokers are not a rank of the deck: use the $jokers count.');
        }
        if (\array_any($suits, static fn (Suit $suit): bool => $suit->isJoker())) {
            throw new \InvalidArgumentException('Joker suits are not a suit of the deck: use the $jokers count.');
        }
        $this->ranks = $ranks;
        $this->suits = $suits;
        $this->backs = $backs ?? ($copies > 1 ? CardBack::cases() : []);
    }

    /**
     * Total number of cards.
     */
    public function count(): int
    {
        return \max(0, $this->copies * \count($this->ranks) * \count($this->suits) + $this->jokers);
    }

    /**
     * @return list<Card> unshuffled
     */
    public function getCards(): array
    {
        $jokerSuits = Suit::jokers();
        $jokers = \array_fill(0, $this->copies, []);
        for ($i = 0; $i < $this->jokers; ++$i) {
            $jokers[\intdiv($i, 2) % $this->copies][] = $jokerSuits[$i % 2];
        }
        $cards = [];
        for ($copy = 0; $copy < $this->copies; ++$copy) {
            $back = [] === $this->backs ? null : $this->backs[$copy % \count($this->backs)];
            foreach ($this->suits as $suit) {
                foreach ($this->ranks as $rank) {
                    $cards[] = new Card($rank, $suit, $back);
                }
            }
            foreach ($jokers[$copy] as $suit) {
                $cards[] = new Card(Rank::Joker, $suit, $back);
            }
        }

        return $cards;
    }

    /**
     * @param Randomizer|null $randomizer pass a seeded one for a reproducible order
     *
     * @return list<Card>
     */
    public function shuffle(?Randomizer $randomizer = null): array
    {
        return \array_values(($randomizer ?? new Randomizer())->shuffleArray($this->getCards()));
    }

    /**
     * A pile to draw from: the first card of the deck is on top, so that an unshuffled deck is drawn in order.
     *
     * @param Randomizer|null $randomizer pass a seeded one for a reproducible order
     */
    public function createPile(bool $shuffle = false, ?Randomizer $randomizer = null): Pile
    {
        return Pile::createFromTop($shuffle ? $this->shuffle($randomizer) : $this->getCards());
    }
}
