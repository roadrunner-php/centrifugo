<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Payload;

use RoadRunner\Centrifugo\Payload\RefreshResponse;
use Testo\Assert;
use Testo\Data\DataProvider;
use Testo\Test;

#[Test]
final class RefreshResponseTest
{
    public static function refreshResponseDataProvider(): \Traversable
    {
        yield [new RefreshResponse(false, null, []), new RefreshResponse()];
        yield [new RefreshResponse(false, 1667892603, []), new RefreshResponse(expireAt: 1667892603)];
        yield [
            new RefreshResponse(false, (new \DateTimeImmutable())->setTimestamp(1667892603), []),
            new RefreshResponse(expireAt: (new \DateTimeImmutable())->setTimestamp(1667892603)),
        ];
    }

    #[DataProvider('refreshResponseDataProvider')]
    public function testRefreshResponse(RefreshResponse $expected, RefreshResponse $actual): void
    {
        Assert::equals($actual, $expected);
    }
}
