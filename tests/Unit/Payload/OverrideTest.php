<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Payload;

use PHPUnit\Framework\TestCase;
use RoadRunner\Centrifugo\Payload\Override;

final class OverrideTest extends TestCase
{
    public function testDefaultValuesNull(): void
    {
        $override = new Override();

        $this->assertNull($override->presence);
        $this->assertNull($override->joinLeave);
        $this->assertNull($override->forcePushJoinLeave);
        $this->assertNull($override->forcePositioning);
        $this->assertNull($override->forceRecovery);
    }
}
