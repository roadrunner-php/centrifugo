<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Request;

use RoadRunner\Centrifugal\Proxy\DTO\V1\RPCResponse as RPCResponseDTO;
use RoadRunner\Centrifugal\Proxy\DTO\V1\RPCResult;
use RoadRunner\Centrifugo\Payload\RPCResponse;
use RoadRunner\Centrifugo\Request\RPC;
use RoadRunner\Centrifugo\Tests\Unit\TestCase;
use Spiral\RoadRunner\Payload;
use Spiral\RoadRunner\WorkerInterface;
use Testo\Assert;
use Testo\Data\DataProvider;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

#[Test]
final class RPCTest extends TestCase
{
    private RPC $rpc;

    public static function mapResponseDataProvider(): \Traversable
    {
        yield [new RPCResponse(), ['data' => '[]']];
        yield [new RPCResponse(['some']), ['data' => '["some"]']];
    }

    public function testRespond(): void
    {
        $worker = $this->createWorker(function (Payload $payload) {
            Assert::equals($payload, new Payload((new RPCResponseDTO(['result' => new RPCResult(['data' => json_encode([])])]))->serializeToString()));
        });

        $refresh = new RPC($worker, '', '', '', '', '', '', [], [], []);

        $refresh->respond(new RPCResponse());
    }

    public function testGetResponseObject(): void
    {
        $ref = new \ReflectionMethod($this->rpc, 'getResponseObject');

        Assert::instanceOf($ref->invoke($this->rpc), RPCResponseDTO::class);
    }

    #[DataProvider('mapResponseDataProvider')]
    public function testMapResponse(RPCResponse $response, array $expected): void
    {
        $ref = new \ReflectionMethod($this->rpc, 'mapResponse');

        /** @var RPCResult $dto */
        $dto = $ref->invoke($this->rpc, $response);

        Assert::same($dto->getData(), $expected['data']);
    }

    #[BeforeTest]
    protected function setUp(): void
    {
        $this->rpc = new RPC(\Mockery::mock(WorkerInterface::class)->shouldIgnoreMissing(), '', '', '', '', '', '', [], [], []);
    }
}
