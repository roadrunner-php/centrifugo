<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Payload;

use PHPUnit\Framework\TestCase;
use RoadRunner\Centrifugo\Payload\Override;
use RoadRunner\Centrifugo\Payload\SubscribeResponse;

final class SubscribeResponseTest extends TestCase
{
    /**
     * @dataProvider subscribeResponseDataProvider
     */
    public function testSubscribeResponse(SubscribeResponse $expected, SubscribeResponse $actual): void
    {
        $this->assertEquals($expected, $actual);
    }

    public function subscribeResponseDataProvider(): \Traversable
    {
        yield [new SubscribeResponse([], [], [], null), new SubscribeResponse()];
        yield [
            new SubscribeResponse([], [], [], new Override()),
            new SubscribeResponse(override: new Override())
        ];
    }
}
