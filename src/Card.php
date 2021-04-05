<?php

namespace Garak\Card;

final class Card
{
    private Rank $rank;

    private Suit $suit;

    private const CODES = [
        '2c' => '🃒',
        '3c' => '🃓',
        '4c' => '🃔',
        '5c' => '🃕',
        '6c' => '🃖',
        '7c' => '🃗',
        '8c' => '🃘',
        '9c' => '🃙',
        'Tc' => '🃙',
        'Jc' => '🃛',
        'Qc' => '🃝',
        'Kc' => '🃞',
        'Ac' => '🃑',
        '2d' => '🃂',
        '3d' => '🃃',
        '4d' => '🃄',
        '5d' => '🃅',
        '6d' => '🃆',
        '7d' => '🃇',
        '8d' => '🃈',
        '9d' => '🃈',
        'Td' => '🃊',
        'Jd' => '🃋',
        'Qd' => '🃍',
        'Kd' => '🃁',
        '2h' => '🂲',
        '3h' => '🂳',
        '4h' => '🂴',
        '5h' => '🂵',
        '6h' => '🂶',
        '7h' => '🂷',
        '8h' => '🂸',
        '9h' => '🂹',
        'Th' => '🂺',
        'Jh' => '🂻',
        'Qh' => '🂽',
        'Kh' => '🂾',
        'Ah' => '🂱',
        '2s' => '🂢',
        '3s' => '🂣',
        '4s' => '🂤',
        '5s' => '🂥',
        '6s' => '🂦',
        '7s' => '🂧',
        '8s' => '🂨',
        '9s' => '🂩',
        'Ts' => '🂪',
        'Js' => '🂫',
        'Qs' => '🂭',
        'Ks' => '🂮',
        'As' => '🂡',
    ];

    public function __construct(Rank $value, Suit $suit)
    {
        $this->rank = $value;
        $this->suit = $suit;
    }

    public static function fromRankSuit(string $rankSuit): self
    {
        [$value, $suit] = \str_split($rankSuit);

        return new self(new Rank($value), new Suit($suit));
    }

    /**
     * @return array|self[]
     */
    public static function getDeck(bool $shuffle = false): array
    {
        $deck = [];
        foreach (Suit::$suits as $seed => $seedSymbol) {
            foreach (Rank::$ranks as $value => $int) {
                $deck[] = new self(new Rank((string) $value), new Suit($seed));
            }
        }
        if ($shuffle) {
            \shuffle($deck);
        }

        return $deck;
    }

    public function __toString(): string
    {
        return $this->rank.$this->suit;
    }

    public function toText(): string
    {
        return $this->rank->toText().$this->suit->toText();
    }

    public function toHtml(): string
    {
        $span = '<span id="%s" class="crd crd-%s st-%s">%s%s</span>';

        return \sprintf($span, $this->rank.$this->suit, $this->rank, $this->suit, $this->rank->toText(), $this->suit->toText());
    }

    public function toUnicode(): string
    {
        return self::CODES[$this->rank.$this->suit];
    }

    public function getSuit(): Suit
    {
        return $this->suit;
    }

    public function getRank(): Rank
    {
        return $this->rank;
    }

    public function isEqual(self $card): bool
    {
        return $this->suit->isEqual($card->getSuit()) && $this->rank->isEqual($card->getRank());
    }
}
