<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Payload;

use PHPUnit\Framework\TestCase;
use RoadRunner\Centrifugo\Payload\PublishResponse;

final class PublishResponseTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $publish = new PublishResponse();

        $this->assertSame([], $publish->data);
        $this->assertFalse($publish->skipHistory);
    }
}
