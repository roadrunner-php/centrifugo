<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Request;

use RoadRunner\Centrifugo\DTO\PublishResult;
use RoadRunner\Centrifugo\Payload\PublishResponse;
use RoadRunner\Centrifugo\Request\Publish;
use RoadRunner\Centrifugo\Tests\Unit\TestCase;
use Spiral\RoadRunner\Payload;
use RoadRunner\Centrifugo\DTO\PublishResponse as PublishResponseDTO;
use Spiral\RoadRunner\WorkerInterface;

final class PublishTest extends TestCase
{
    private Publish $publish;

    protected function setUp(): void
    {
        $this->publish = new Publish($this->createMock(WorkerInterface::class), '', '', '', '', '', '', [], [], []);
    }

    public function testRespond(): void
    {
        $worker = $this->createWorker(function (Payload $payload) {
            $this->assertEquals(
                new Payload((new PublishResponseDTO(['result' => new PublishResult()]))->serializeToString()),
                $payload
            );
        });

        $publish = new Publish($worker, '', '', '', '', '', '', [], [], []);

        $publish->respond(new PublishResponse());
    }

    public function testGetResponseObject(): void
    {
        $ref = new \ReflectionMethod($this->publish, 'getResponseObject');

        $this->assertInstanceOf(PublishResponseDTO::class, $ref->invoke($this->publish));
    }

    /**
     * @dataProvider mapResponseDataProvider
     */
    public function testMapResponse(PublishResponse $response, array $expected): void
    {
        $ref = new \ReflectionMethod($this->publish, 'mapResponse');

        /** @var PublishResult $dto */
        $dto = $ref->invoke($this->publish, $response);

        $this->assertSame($expected['data'], $dto->getData());
        $this->assertSame($expected['skip_history'], $dto->getSkipHistory());
    }

    public function mapResponseDataProvider(): \Traversable
    {
        yield [new PublishResponse(), ['data' => '', 'skip_history' => false]];
        yield [new PublishResponse(skipHistory: true), ['data' => '', 'skip_history' => true]];
        yield [new PublishResponse(['some']), ['data' => '["some"]', 'skip_history' => false]];
        yield [new PublishResponse(['some'], skipHistory: true), ['data' => '["some"]', 'skip_history' => true]];
    }
}
