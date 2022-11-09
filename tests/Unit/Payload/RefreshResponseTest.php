<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Payload;

use PHPUnit\Framework\TestCase;
use RoadRunner\Centrifugo\Payload\RefreshResponse;

final class RefreshResponseTest extends TestCase
{
    /**
     * @dataProvider refreshResponseDataProvider
     */
    public function testRefreshResponse(RefreshResponse $expected, RefreshResponse $actual): void
    {
        $this->assertEquals($expected, $actual);
    }

    public function refreshResponseDataProvider(): \Traversable
    {
        yield [new RefreshResponse(false, null, []), new RefreshResponse()];
        yield [new RefreshResponse(false, 1667892603, []), new RefreshResponse(expireAt: 1667892603)];
        yield [
            new RefreshResponse(false, (new \DateTimeImmutable())->setTimestamp(1667892603), []),
            new RefreshResponse(expireAt: (new \DateTimeImmutable())->setTimestamp(1667892603))
        ];
    }
}
