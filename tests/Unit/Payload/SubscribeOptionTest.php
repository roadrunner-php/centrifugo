<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Payload;

use RoadRunner\Centrifugo\Payload\Override;
use RoadRunner\Centrifugo\Payload\SubscribeOption;
use Testo\Assert;
use Testo\Data\DataProvider;
use Testo\Test;

#[Test]
final class SubscribeOptionTest
{
    public static function subscribeOptionDataProvider(): \Traversable
    {
        yield [new SubscribeOption(null, [], [], null), new SubscribeOption()];
        yield [new SubscribeOption(1667892603, [], [], null), new SubscribeOption(expireAt: 1667892603)];
        yield [
            new SubscribeOption((new \DateTimeImmutable())->setTimestamp(1667892603), [], [], null),
            new SubscribeOption(expireAt: (new \DateTimeImmutable())->setTimestamp(1667892603)),
        ];
        yield [new SubscribeOption(null, [], [], new Override()), new SubscribeOption(override: new Override())];
    }

    #[DataProvider('subscribeOptionDataProvider')]
    public function testSubscribeOption(SubscribeOption $expected, SubscribeOption $actual): void
    {
        Assert::equals($actual, $expected);
    }
}
