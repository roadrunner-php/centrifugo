<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Payload;

use RoadRunner\Centrifugo\Payload\SubRefreshResponse;
use Testo\Assert;
use Testo\Data\DataProvider;
use Testo\Test;

#[Test]
final class SubRefreshResponseTest
{
    public static function refreshResponseDataProvider(): \Traversable
    {
        yield [new SubRefreshResponse(false, null, []), new SubRefreshResponse()];
        yield [new SubRefreshResponse(false, 1667892603, []), new SubRefreshResponse(expireAt: 1667892603)];
        yield [
            new SubRefreshResponse(false, (new \DateTimeImmutable())->setTimestamp(1667892603), []),
            new SubRefreshResponse(expireAt: (new \DateTimeImmutable())->setTimestamp(1667892603)),
        ];
    }

    #[DataProvider('refreshResponseDataProvider')]
    public function testRefreshResponse(SubRefreshResponse $expected, SubRefreshResponse $actual): void
    {
        Assert::equals($actual, $expected);
    }
}
