<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Payload;

use Testo\Test;
use Testo\Data\DataProvider;
use Testo\Assert;
use RoadRunner\Centrifugo\Payload\Override;
use RoadRunner\Centrifugo\Payload\SubscribeResponse;

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
