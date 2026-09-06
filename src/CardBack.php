<?php

namespace Garak\Card;

enum CardBack: string
{
    case Red = 'red';
    case Blue = 'blue';

    /**
     * Single-char representation, used in string serialization (see Card::fromRankSuit()).
     */
    public function toText(): string
    {
        return match ($this) {
            self::Red => 'r',
            self::Blue => 'b',
        };
    }

    public static function fromText(string $text): self
    {
        return \array_find(self::cases(), static fn (self $back): bool => $back->toText() === $text)
            ?? throw new \ValueError(\sprintf('"%s" is not a valid back text for enum %s', $text, self::class));
    }

    public function isEqual(self $back): bool
    {
        return $this === $back;
    }
}
