<?php

namespace Garak\Card;

/**
 * The cards played in a turn of a trick-taking game, in playing order.
 * The keys of the cards identify who played them (e.g. a seat or a player name).
 */
abstract class CardsTrick
{
    /** @param array<int|string, Card> $cards in playing order, keyed by player */
    public function __construct(protected array $cards)
    {
    }

    /**
     * @return array<int|string, Card>
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    /**
     * The suit of the first card played, or null when no card was played yet.
     */
    public function getLedSuit(): ?Suit
    {
        $first = \array_key_first($this->cards);

        return null === $first ? null : $this->cards[$first]->getSuit();
    }

    /**
     * The rule shared by most trick-taking games (bridge, whist, hearts, spades...): the highest trump
     * wins when at least one was played, otherwise the highest card of the led suit; other cards never win.
     * Ties (possible with more than one deck) go to the card played first.
     * Override for other rules, and getRankOrder() for another order of the ranks.
     *
     * @throws \DomainException when no card was played
     */
    public function getWinningCard(?Suit $trump): Card
    {
        $led = $this->getLedSuit() ?? throw new \DomainException('No cards in trick.');
        $candidates = \array_filter($this->cards, static fn (Card $card): bool => $card->getSuit() === $trump);
        if ([] === $candidates) {
            $candidates = \array_filter($this->cards, static fn (Card $card): bool => $card->getSuit() === $led);
        }
        $winner = $this->getRankOrder()->highest($candidates);
        \assert(null !== $winner);

        return $winner;
    }

    /**
     * The key of the player who played the winning card, see getWinningCard().
     * The card is looked up by identity first, then by equality (for overrides returning a new instance).
     */
    public function getWinningKey(?Suit $trump): int|string
    {
        $winner = $this->getWinningCard($trump);

        return \array_find_key($this->cards, static fn (Card $card): bool => $card === $winner)
            ?? \array_find_key($this->cards, static fn (Card $card): bool => $card->isEqual($winner))
            ?? throw new \LogicException(\sprintf('The winning card %s is not in the trick.', $winner));
    }

    /**
     * Whether the card can be played from the hand in this trick: it must be in the hand,
     * and follow the led suit when the hand has any card of it.
     */
    public function canPlay(Card $card, Hand $hand): bool
    {
        if (!$hand->has($card)) {
            return false;
        }
        $led = $this->getLedSuit();

        return null === $led || $card->getSuit() === $led || !$hand->hasSuit($led);
    }

    protected function getRankOrder(): RankOrder
    {
        return RankOrder::aceHigh();
    }
}
