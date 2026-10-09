<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Payload;

use Testo\Test;
use Testo\Assert;
use RoadRunner\Centrifugo\Payload\RPCResponse;

#[Test]
final class RPCResponseTest
{
    public function testDefaultValue(): void
    {
        $rpc = new RPCResponse();

        Assert::same($rpc->data, []);
    }
}
