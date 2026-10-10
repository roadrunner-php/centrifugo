<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Payload;

use RoadRunner\Centrifugo\Payload\RPCResponse;
use Testo\Assert;
use Testo\Test;

#[Test]
final class RPCResponseTest
{
    public function testDefaultValue(): void
    {
        $rpc = new RPCResponse();

        Assert::same($rpc->data, []);
    }
}
