<?php

namespace Garak\Card;

/**
 * @deprecated since 0.13, to be removed in 1.0: it cannot be instantiated in a meaningful way.
 *             Compare hands in your game with a CardValues, a RankOrder, or your own evaluator.
 */
abstract class HandsTrick
{
    /** @var array<int|string, Hand> */
    protected array $hands;

    abstract public function getWinningHand(?Suit $suit): Hand;
}
