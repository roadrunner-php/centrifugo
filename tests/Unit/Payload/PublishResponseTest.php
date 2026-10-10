<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Payload;

use RoadRunner\Centrifugo\Payload\PublishResponse;
use Testo\Assert;
use Testo\Test;

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
