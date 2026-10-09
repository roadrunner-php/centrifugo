<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Payload;

use Testo\Test;
use Testo\Assert;
use RoadRunner\Centrifugo\Payload\Override;

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
