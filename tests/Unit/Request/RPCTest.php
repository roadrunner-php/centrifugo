<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Request;

use RoadRunner\Centrifugo\DTO\RPCResult;
use RoadRunner\Centrifugo\Payload\RPCResponse;
use RoadRunner\Centrifugo\Request\RPC;
use RoadRunner\Centrifugo\Tests\Unit\TestCase;
use Spiral\RoadRunner\Payload;
use RoadRunner\Centrifugo\DTO\RPCResponse as RPCResponseDTO;
use Spiral\RoadRunner\WorkerInterface;

final class RPCTest extends TestCase
{
    private RPC $rpc;

    protected function setUp(): void
    {
        $this->rpc = new RPC($this->createMock(WorkerInterface::class), '', '', '', '', '', '', [], [], []);
    }

    public function testRespond(): void
    {
        $worker = $this->createWorker(function (Payload $payload) {
            $this->assertEquals(
                new Payload((new RPCResponseDTO(['result' => new RPCResult(['data' => json_encode([])])]))->serializeToString()),
                $payload
            );
        });

        $refresh = new RPC($worker, '', '', '', '', '',  '', [], [], []);

        $refresh->respond(new RPCResponse());
    }

    public function testGetResponseObject(): void
    {
        $ref = new \ReflectionMethod($this->rpc, 'getResponseObject');

        $this->assertInstanceOf(RPCResponseDTO::class, $ref->invoke($this->rpc));
    }

    /**
     * @dataProvider mapResponseDataProvider
     */
    public function testMapResponse(RPCResponse $response, array $expected): void
    {
        $ref = new \ReflectionMethod($this->rpc, 'mapResponse');

        /** @var RPCResult $dto */
        $dto = $ref->invoke($this->rpc, $response);

        $this->assertSame($expected['data'], $dto->getData());
    }

    public function mapResponseDataProvider(): \Traversable
    {
        yield [new RPCResponse(), ['data' => '[]']];
        yield [new RPCResponse(['some']), ['data' => '["some"]']];
    }
}
