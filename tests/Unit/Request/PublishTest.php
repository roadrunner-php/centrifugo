<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Request;

use RoadRunner\Centrifugal\Proxy\DTO\V1\PublishResponse as PublishResponseDTO;
use RoadRunner\Centrifugal\Proxy\DTO\V1\PublishResult;
use RoadRunner\Centrifugo\Payload\PublishResponse;
use RoadRunner\Centrifugo\Request\Publish;
use RoadRunner\Centrifugo\Tests\Unit\TestCase;
use Spiral\RoadRunner\Payload;
use Spiral\RoadRunner\WorkerInterface;
use Testo\Assert;
use Testo\Data\DataProvider;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

#[Test]
final class PublishTest extends TestCase
{
    private Publish $publish;

    public static function mapResponseDataProvider(): \Traversable
    {
        yield [new PublishResponse(), ['data' => '', 'skip_history' => false]];
        yield [new PublishResponse(skipHistory: true), ['data' => '', 'skip_history' => true]];
        yield [new PublishResponse(['some']), ['data' => '["some"]', 'skip_history' => false]];
        yield [new PublishResponse(['some'], skipHistory: true), ['data' => '["some"]', 'skip_history' => true]];
    }

    public function testRespond(): void
    {
        $worker = $this->createWorker(function (Payload $payload) {
            Assert::equals($payload, new Payload((new PublishResponseDTO(['result' => new PublishResult()]))->serializeToString()));
        });

        $publish = new Publish($worker, '', '', '', '', '', '', [], [], []);

        $publish->respond(new PublishResponse());
    }

    public function testGetResponseObject(): void
    {
        $ref = new \ReflectionMethod($this->publish, 'getResponseObject');

        Assert::instanceOf($ref->invoke($this->publish), PublishResponseDTO::class);
    }

    #[DataProvider('mapResponseDataProvider')]
    public function testMapResponse(PublishResponse $response, array $expected): void
    {
        $ref = new \ReflectionMethod($this->publish, 'mapResponse');

        /** @var PublishResult $dto */
        $dto = $ref->invoke($this->publish, $response);

        Assert::same($dto->getData(), $expected['data']);
        Assert::same($dto->getSkipHistory(), $expected['skip_history']);
    }

    #[BeforeTest]
    protected function setUp(): void
    {
        $this->publish = new Publish(\Mockery::mock(WorkerInterface::class)->shouldIgnoreMissing(), '', '', '', '', '', '', [], [], []);
    }
}
