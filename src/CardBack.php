<?php

namespace Garak\Card;

enum CardBack: string
{
    case Red = 'red';
    case Blue = 'blue';

    public function isEqual(self $back): bool
    {
        return $this === $back;
    }
}
