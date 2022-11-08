<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Payload;

use PHPUnit\Framework\TestCase;
use RoadRunner\Centrifugo\Payload\RPCResponse;

final class RPCResponseTest extends TestCase
{
    public function testDefaultValue(): void
    {
        $rpc = new RPCResponse();

        $this->assertSame([], $rpc->data);
    }
}
