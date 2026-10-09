<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Payload;

use Testo\Test;
use Testo\Data\DataProvider;
use Testo\Assert;
use RoadRunner\Centrifugo\Payload\ConnectResponse;

#[Test]
final class ConnectResponseTest
{
    public static function connectResponseDataProvider(): \Traversable
    {
        yield [new ConnectResponse('', null, [], [], [], [], []), new ConnectResponse()];
        yield [
            new ConnectResponse('', 1667892603, [], [], [], [], []),
            new ConnectResponse(expireAt: 1667892603),
        ];
        yield [
            new ConnectResponse('', (new \DateTimeImmutable())->setTimestamp(1667892603), [], [], [], [], []),
            new ConnectResponse(expireAt: (new \DateTimeImmutable())->setTimestamp(1667892603)),
        ];
    }

    #[DataProvider('connectResponseDataProvider')]
    public function testConnectResponse(ConnectResponse $expected, ConnectResponse $actual): void
    {
        Assert::equals($actual, $expected);
    }
}
