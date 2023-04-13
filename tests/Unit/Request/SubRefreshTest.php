<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Request;

use RoadRunner\Centrifugal\Proxy\DTO\V1\SubRefreshResult;
use RoadRunner\Centrifugo\Payload\SubRefreshResponse;
use RoadRunner\Centrifugo\Request\SubRefresh;
use RoadRunner\Centrifugo\Tests\Unit\TestCase;
use Spiral\RoadRunner\Payload;
use RoadRunner\Centrifugal\Proxy\DTO\V1\SubRefreshResponse as RefreshResponseDTO;
use Spiral\RoadRunner\WorkerInterface;

final class SubRefreshTest extends TestCase
{
    private SubRefresh $refresh;

    protected function setUp(): void
    {
        $this->refresh = new SubRefresh(
            worker: $this->createMock(WorkerInterface::class),
            client: '',
            transport: '',
            protocol: '',
            encoding: '', user: '',
            channel: '',
            meta: [],
            headers: []
        );
    }

    public function testRespond(): void
    {
        $worker = $this->createWorker(function (Payload $payload) {
            $this->assertEquals(
                new Payload((new RefreshResponseDTO(['result' => new SubRefreshResult()]))->serializeToString()),
                $payload
            );
        });

        $refresh = new SubRefresh($worker, '', '', '', '', '',  '', [], []);

        $refresh->respond(new SubRefreshResponse());
    }

    public function testGetResponseObject(): void
    {
        $ref = new \ReflectionMethod($this->refresh, 'getResponseObject');

        $this->assertInstanceOf(RefreshResponseDTO::class, $ref->invoke($this->refresh));
    }

    /**
     * @dataProvider mapResponseDataProvider
     */
    public function testMapResponse(SubRefreshResponse $response, array $expected): void
    {
        $ref = new \ReflectionMethod($this->refresh, 'mapResponse');

        /** @var SubRefreshResult $dto */
        $dto = $ref->invoke($this->refresh, $response);

        $this->assertSame($expected['expired'], $dto->getExpired());
        $this->assertSame($expected['expire_at'], $dto->getExpireAt());
        $this->assertSame($expected['info'], $dto->getInfo());
    }

    public static function mapResponseDataProvider(): \Traversable
    {
        yield [new SubRefreshResponse(), ['expired' => false, 'expire_at' => 0, 'info' => '']];
        yield [new SubRefreshResponse(expired: true), ['expired' => true, 'expire_at' => 0, 'info' => '']];
        yield [new SubRefreshResponse(expireAt: 1111), ['expired' => false, 'expire_at' => 1111, 'info' => '']];
        yield [
            new SubRefreshResponse(expireAt: (new \DateTimeImmutable())->setTimestamp(1111)),
            ['expired' => false, 'expire_at' => 1111, 'info' => '']
        ];
        yield [new SubRefreshResponse(info: ['some']), ['expired' => false, 'expire_at' => 0, 'info' => '["some"]']];
        yield [
            new SubRefreshResponse(true, (new \DateTimeImmutable())->setTimestamp(1111), ['some']),
            ['expired' => true, 'expire_at' => 1111, 'info' => '["some"]']
        ];
    }
}
