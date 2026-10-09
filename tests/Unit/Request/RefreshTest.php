<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Request;

use Testo\Test;
use Testo\Assert;
use Testo\Data\DataProvider;
use Testo\Lifecycle\BeforeTest;
use RoadRunner\Centrifugal\Proxy\DTO\V1\RefreshResult;
use RoadRunner\Centrifugo\Payload\RefreshResponse;
use RoadRunner\Centrifugo\Request\Refresh;
use RoadRunner\Centrifugo\Tests\Unit\TestCase;
use Spiral\RoadRunner\Payload;
use RoadRunner\Centrifugal\Proxy\DTO\V1\RefreshResponse as RefreshResponseDTO;
use Spiral\RoadRunner\WorkerInterface;

#[Test]
final class RefreshTest extends TestCase
{
    private Refresh $refresh;

    public static function mapResponseDataProvider(): \Traversable
    {
        yield [new RefreshResponse(), ['expired' => false, 'expire_at' => 0, 'info' => '']];
        yield [new RefreshResponse(expired: true), ['expired' => true, 'expire_at' => 0, 'info' => '']];
        yield [new RefreshResponse(expireAt: 1111), ['expired' => false, 'expire_at' => 1111, 'info' => '']];
        yield [
            new RefreshResponse(expireAt: (new \DateTimeImmutable())->setTimestamp(1111)),
            ['expired' => false, 'expire_at' => 1111, 'info' => ''],
        ];
        yield [new RefreshResponse(info: ['some']), ['expired' => false, 'expire_at' => 0, 'info' => '["some"]']];
        yield [
            new RefreshResponse(true, (new \DateTimeImmutable())->setTimestamp(1111), ['some']),
            ['expired' => true, 'expire_at' => 1111, 'info' => '["some"]'],
        ];
    }

    public function testRespond(): void
    {
        $worker = $this->createWorker(function (Payload $payload) {
            Assert::equals($payload, new Payload((new RefreshResponseDTO(['result' => new RefreshResult()]))->serializeToString()));
        });

        $refresh = new Refresh($worker, '', '', '', '', '', [], []);

        $refresh->respond(new RefreshResponse());
    }

    public function testGetResponseObject(): void
    {
        $ref = new \ReflectionMethod($this->refresh, 'getResponseObject');

        Assert::instanceOf($ref->invoke($this->refresh), RefreshResponseDTO::class);
    }

    #[DataProvider('mapResponseDataProvider')]
    public function testMapResponse(RefreshResponse $response, array $expected): void
    {
        $ref = new \ReflectionMethod($this->refresh, 'mapResponse');

        /** @var RefreshResult $dto */
        $dto = $ref->invoke($this->refresh, $response);

        Assert::same($dto->getExpired(), $expected['expired']);
        Assert::same($dto->getExpireAt(), $expected['expire_at']);
        Assert::same($dto->getInfo(), $expected['info']);
    }

    #[BeforeTest]
    protected function setUp(): void
    {
        $this->refresh = new Refresh(\Mockery::mock(WorkerInterface::class)->shouldIgnoreMissing(), '', '', '', '', '', [], []);
    }
}
