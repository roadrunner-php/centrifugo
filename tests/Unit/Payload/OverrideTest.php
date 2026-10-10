<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Payload;

use RoadRunner\Centrifugo\Payload\Override;
use Testo\Assert;
use Testo\Test;

#[Test]
final class OverrideTest
{
    public function testDefaultValuesNull(): void
    {
        $override = new Override();

        Assert::null($override->presence);
        Assert::null($override->joinLeave);
        Assert::null($override->forcePushJoinLeave);
        Assert::null($override->forcePositioning);
        Assert::null($override->forceRecovery);
    }
}
