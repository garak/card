<?php

namespace Garak\Card\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HandsTrickTest extends TestCase
{
    #[Test]
    public function getWinningHand(): void
    {
        $hand = HandStub::createFromString('6s,4h,3s,Td,6c,3d,3h,Kc,Qc,Tc,7d,2c,6d');
        $trick = new HandsTrickStub([$hand]);
        self::assertSame($hand, $trick->getWinningHand(null));
    }
}
