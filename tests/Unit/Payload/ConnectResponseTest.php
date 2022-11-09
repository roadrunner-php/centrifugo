<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Payload;

use PHPUnit\Framework\TestCase;
use RoadRunner\Centrifugo\Payload\ConnectResponse;

final class ConnectResponseTest extends TestCase
{
    /**
     * @dataProvider connectResponseDataProvider
     */
    public function testConnectResponse(ConnectResponse $expected, ConnectResponse $actual): void
    {
        $this->assertEquals($expected, $actual);
    }

    public function connectResponseDataProvider(): \Traversable
    {
        yield [new ConnectResponse('', null, [], [], [], [], []), new ConnectResponse()];
        yield [
            new ConnectResponse('', 1667892603, [], [], [], [], []),
            new ConnectResponse(expireAt: 1667892603)
        ];
        yield [
            new ConnectResponse('', (new \DateTimeImmutable())->setTimestamp(1667892603), [], [], [], [], []),
            new ConnectResponse(expireAt: (new \DateTimeImmutable())->setTimestamp(1667892603))
        ];
    }
}
