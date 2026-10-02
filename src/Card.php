<?php

namespace Garak\Card;

use Random\Randomizer;

final readonly class Card implements \Stringable
{
    /**
     * Placeholder for the rank and suit of a card seen from its back, see toHiddenString().
     */
    public const HIDDEN = '??';

    public function __construct(
        private Rank $rank,
        private Suit $suit,
        private ?CardBack $back = null
    ) {
    }

    /**
     * Accepts a 2-char string (rank and suit, e.g. "As") or a 3-char string
     * with a trailing back (e.g. "Asr" for an ace of spades with red back).
     */
    public static function fromRankSuit(string $rankSuit): self
    {
        $length = \strlen($rankSuit);
        if ($length < 2 || $length > 3) {
            throw new \InvalidArgumentException(\sprintf('Invalid card string "%s": expected 2 or 3 characters.', $rankSuit));
        }
        $back = 3 === $length ? CardBack::fromText($rankSuit[2]) : null;

        return new self(Rank::from($rankSuit[0]), Suit::from($rankSuit[1]), $back);
    }

    /**
     * Shortcut for the most common decks. See Deck for stripped decks, a given number of jokers, or custom backs.
     *
     * @param Randomizer|null $randomizer pass a seeded one for a reproducible shuffle
     *
     * @return array|self[]
     */
    public static function getDeck(bool $shuffle = false, int $num = 1, bool $allowJokers = false, ?Randomizer $randomizer = null): array
    {
        $deck = new Deck(copies: $num, jokers: $allowJokers ? 2 * $num : 0);

        return $shuffle ? $deck->shuffle($randomizer) : $deck->getCards();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * String representation, parsable by fromRankSuit().
     * The back (if any) is included only when explicitly requested.
     */
    public function toString(bool $withBack = false): string
    {
        $string = $this->rank->value.$this->suit->value;
        if ($withBack && null !== $this->back) {
            $string .= $this->back->toText();
        }

        return $string;
    }

    /**
     * Representation of the card as seen from its back, e.g. for a card face down or in an opponent's hand:
     * the rank and suit are replaced by "??", the back (if any) is kept (e.g. "??r").
     */
    public function toHiddenString(): string
    {
        return self::HIDDEN.($this->back?->toText() ?? '');
    }

    public function toText(): string
    {
        return $this->rank->toText().$this->suit->toText();
    }

    public function toHtml(string $template = '<span id="%s" class="crd crd-%s st-%s">%s%s</span>'): string
    {
        return \sprintf($template, $this->rank->value.$this->suit->value, $this->rank->value, $this->suit->value, $this->rank->toText(), $this->suit->toText());
    }

    public function toUnicode(): string
    {
        return CardCode::from($this->rank->value.$this->suit->value)->unicode();
    }

    public function getSuit(): Suit
    {
        return $this->suit;
    }

    public function getRank(): Rank
    {
        return $this->rank;
    }

    public function getBack(): ?CardBack
    {
        return $this->back;
    }

    public function getColor(): Color
    {
        return $this->suit->getColor();
    }

    public function isJoker(): bool
    {
        return $this->rank->isJoker();
    }

    public function isSameFace(self $card): bool
    {
        return $this->suit->isEqual($card->suit) && $this->rank->isEqual($card->rank);
    }

    public function isEqual(self $card): bool
    {
        if (null === $this->back || null === $card->back) {
            return null === $this->back && null === $card->back && $this->isSameFace($card);
        }

        return $this->isSameFace($card) && $this->back->isEqual($card->back);
    }
}
