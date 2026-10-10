<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Payload;

use RoadRunner\Centrifugo\Payload\Override;
use RoadRunner\Centrifugo\Payload\SubscribeResponse;
use Testo\Assert;
use Testo\Data\DataProvider;
use Testo\Test;

#[Test]
final class SubscribeResponseTest
{
    public static function subscribeResponseDataProvider(): \Traversable
    {
        yield [new SubscribeResponse([], [], [], null), new SubscribeResponse()];
        yield [
            new SubscribeResponse([], [], [], new Override()),
            new SubscribeResponse(override: new Override()),
        ];
    }

    #[DataProvider('subscribeResponseDataProvider')]
    public function testSubscribeResponse(SubscribeResponse $expected, SubscribeResponse $actual): void
    {
        Assert::equals($actual, $expected);
    }
}
