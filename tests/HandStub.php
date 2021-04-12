<?php

namespace Garak\Card\Test;

use Garak\Card\Hand;

final class HandStub extends Hand
{
    public function __construct(array $cards, bool $start = true, ?callable $checking = null, ?callable $sorting = null)
    {
        if (null === $checking) {
            $checking = static function (array $cards): bool {
                return 13 === \count($cards);
            };
        }
        if ($start && false === $checking($cards)) {
            throw new \InvalidArgumentException('Invalid starting hand.');
        }
        $this->cards = $cards;
        $this->sorting = $sorting ?? static function (): void {};
    }
}
