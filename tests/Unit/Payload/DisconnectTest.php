<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Payload;

use RoadRunner\Centrifugo\Payload\Disconnect;
use Testo\Assert;
use Testo\Test;

#[Test]
final class DisconnectTest
{
    public function testReconnectDefaultFalse(): void
    {
        $disconnect = new Disconnect(1, 'foo');

        Assert::false($disconnect->reconnect);
    }
}
