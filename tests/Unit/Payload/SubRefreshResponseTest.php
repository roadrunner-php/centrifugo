<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Payload;

use PHPUnit\Framework\TestCase;
use RoadRunner\Centrifugo\Payload\SubRefreshResponse;

final class SubRefreshResponseTest extends TestCase
{
    /**
     * @dataProvider refreshResponseDataProvider
     */
    public function testRefreshResponse(SubRefreshResponse $expected, SubRefreshResponse $actual): void
    {
        $this->assertEquals($expected, $actual);
    }

    public static function refreshResponseDataProvider(): \Traversable
    {
        yield [new SubRefreshResponse(false, null, []), new SubRefreshResponse()];
        yield [new SubRefreshResponse(false, 1667892603, []), new SubRefreshResponse(expireAt: 1667892603)];
        yield [
            new SubRefreshResponse(false, (new \DateTimeImmutable())->setTimestamp(1667892603), []),
            new SubRefreshResponse(expireAt: (new \DateTimeImmutable())->setTimestamp(1667892603))
        ];
    }
}
