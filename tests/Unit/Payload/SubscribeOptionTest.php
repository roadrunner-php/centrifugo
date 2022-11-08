<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Payload;

use PHPUnit\Framework\TestCase;
use RoadRunner\Centrifugo\Payload\Override;
use RoadRunner\Centrifugo\Payload\SubscribeOption;

final class SubscribeOptionTest extends TestCase
{
    /**
     * @dataProvider subscribeOptionDataProvider
     */
    public function testSubscribeOption(SubscribeOption $expected, SubscribeOption $actual): void
    {
        $this->assertEquals($expected, $actual);
    }

    public function subscribeOptionDataProvider(): \Traversable
    {
        yield [new SubscribeOption(null, [], [], null), new SubscribeOption()];
        yield [new SubscribeOption(1667892603, [], [], null), new SubscribeOption(expireAt: 1667892603)];
        yield [
            new SubscribeOption((new \DateTimeImmutable())->setTimestamp(1667892603), [], [], null),
            new SubscribeOption(expireAt: (new \DateTimeImmutable())->setTimestamp(1667892603))
        ];
        yield [new SubscribeOption(null, [], [], new Override()), new SubscribeOption(override: new Override())];
    }
}
