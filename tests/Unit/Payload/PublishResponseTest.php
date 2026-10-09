<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Payload;

use Testo\Test;
use Testo\Assert;
use RoadRunner\Centrifugo\Payload\PublishResponse;

#[Test]
final class PublishResponseTest
{
    public function testDefaultValues(): void
    {
        $publish = new PublishResponse();

        Assert::same($publish->data, []);
        Assert::false($publish->skipHistory);
    }
}
