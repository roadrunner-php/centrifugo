<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Request;

use RoadRunner\Centrifugal\Proxy\DTO\V1\SubRefreshResponse as RefreshResponseDTO;
use RoadRunner\Centrifugal\Proxy\DTO\V1\SubRefreshResult;
use RoadRunner\Centrifugo\Payload\SubRefreshResponse;
use RoadRunner\Centrifugo\Request\SubRefresh;
use RoadRunner\Centrifugo\Tests\Unit\TestCase;
use Spiral\RoadRunner\Payload;
use Spiral\RoadRunner\WorkerInterface;
use Testo\Assert;
use Testo\Data\DataProvider;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

#[Test]
final class SubRefreshTest extends TestCase
{
    private SubRefresh $refresh;

    public static function mapResponseDataProvider(): \Traversable
    {
        yield [new SubRefreshResponse(), ['expired' => false, 'expire_at' => 0, 'info' => '']];
        yield [new SubRefreshResponse(expired: true), ['expired' => true, 'expire_at' => 0, 'info' => '']];
        yield [new SubRefreshResponse(expireAt: 1111), ['expired' => false, 'expire_at' => 1111, 'info' => '']];
        yield [
            new SubRefreshResponse(expireAt: (new \DateTimeImmutable())->setTimestamp(1111)),
            ['expired' => false, 'expire_at' => 1111, 'info' => ''],
        ];
        yield [new SubRefreshResponse(info: ['some']), ['expired' => false, 'expire_at' => 0, 'info' => '["some"]']];
        yield [
            new SubRefreshResponse(true, (new \DateTimeImmutable())->setTimestamp(1111), ['some']),
            ['expired' => true, 'expire_at' => 1111, 'info' => '["some"]'],
        ];
    }

    public function testRespond(): void
    {
        $worker = $this->createWorker(function (Payload $payload) {
            Assert::equals($payload, new Payload((new RefreshResponseDTO(['result' => new SubRefreshResult()]))->serializeToString()));
        });

        $refresh = new SubRefresh($worker, '', '', '', '', '', '', [], []);

        $refresh->respond(new SubRefreshResponse());
    }

    public function testGetResponseObject(): void
    {
        $ref = new \ReflectionMethod($this->refresh, 'getResponseObject');

        Assert::instanceOf($ref->invoke($this->refresh), RefreshResponseDTO::class);
    }

    #[DataProvider('mapResponseDataProvider')]
    public function testMapResponse(SubRefreshResponse $response, array $expected): void
    {
        $ref = new \ReflectionMethod($this->refresh, 'mapResponse');

        /** @var SubRefreshResult $dto */
        $dto = $ref->invoke($this->refresh, $response);

        Assert::same($dto->getExpired(), $expected['expired']);
        Assert::same($dto->getExpireAt(), $expected['expire_at']);
        Assert::same($dto->getInfo(), $expected['info']);
    }

    #[BeforeTest]
    protected function setUp(): void
    {
        $this->refresh = new SubRefresh(
            worker: \Mockery::mock(WorkerInterface::class)->shouldIgnoreMissing(),
            client: '',
            transport: '',
            protocol: '',
            encoding: '',
            user: '',
            channel: '',
            meta: [],
            headers: [],
        );
    }
}
