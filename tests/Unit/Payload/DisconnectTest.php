<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Payload;

use Testo\Test;
use Testo\Assert;
use RoadRunner\Centrifugo\Payload\Disconnect;

#[Test]
final class DisconnectTest
{
    public function testReconnectDefaultFalse(): void
    {
        $disconnect = new Disconnect(1, 'foo');

        Assert::false($disconnect->reconnect);
    }
}
