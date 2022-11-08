<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Payload;

use PHPUnit\Framework\TestCase;
use RoadRunner\Centrifugo\Payload\Disconnect;

final class DisconnectTest extends TestCase
{
    public function testReconnectDefaultFalse(): void
    {
        $disconnect = new Disconnect(1, 'foo');

        $this->assertFalse($disconnect->reconnect);
    }
}
