<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Request;

use RoadRunner\Centrifugo\DTO\RefreshResult;
use RoadRunner\Centrifugo\Payload\RefreshResponse;
use RoadRunner\Centrifugo\Request\Refresh;
use RoadRunner\Centrifugo\Tests\Unit\TestCase;
use Spiral\RoadRunner\Payload;
use RoadRunner\Centrifugo\DTO\RefreshResponse as RefreshResponseDTO;
use Spiral\RoadRunner\WorkerInterface;

final class RefreshTest extends TestCase
{
    private Refresh $refresh;

    protected function setUp(): void
    {
        $this->refresh = new Refresh($this->createMock(WorkerInterface::class), '', '', '', '', '', [], []);
    }

    public function testRespond(): void
    {
        $worker = $this->createWorker(function (Payload $payload) {
            $this->assertEquals(
                new Payload((new RefreshResponseDTO(['result' => new RefreshResult()]))->serializeToString()),
                $payload
            );
        });

        $refresh = new Refresh($worker, '', '', '', '', '',  [], []);

        $refresh->respond(new RefreshResponse());
    }

    public function testGetResponseObject(): void
    {
        $ref = new \ReflectionMethod($this->refresh, 'getResponseObject');

        $this->assertInstanceOf(RefreshResponseDTO::class, $ref->invoke($this->refresh));
    }

    /**
     * @dataProvider mapResponseDataProvider
     */
    public function testMapResponse(RefreshResponse $response, array $expected): void
    {
        $ref = new \ReflectionMethod($this->refresh, 'mapResponse');

        /** @var RefreshResult $dto */
        $dto = $ref->invoke($this->refresh, $response);

        $this->assertSame($expected['expired'], $dto->getExpired());
        $this->assertSame($expected['expire_at'], $dto->getExpireAt());
        $this->assertSame($expected['info'], $dto->getInfo());
    }

    public function mapResponseDataProvider(): \Traversable
    {
        yield [new RefreshResponse(), ['expired' => false, 'expire_at' => 0, 'info' => '']];
        yield [new RefreshResponse(expired: true), ['expired' => true, 'expire_at' => 0, 'info' => '']];
        yield [new RefreshResponse(expireAt: 1111), ['expired' => false, 'expire_at' => 1111, 'info' => '']];
        yield [
            new RefreshResponse(expireAt: (new \DateTimeImmutable())->setTimestamp(1111)),
            ['expired' => false, 'expire_at' => 1111, 'info' => '']
        ];
        yield [new RefreshResponse(info: ['some']), ['expired' => false, 'expire_at' => 0, 'info' => '["some"]']];
        yield [
            new RefreshResponse(true, (new \DateTimeImmutable())->setTimestamp(1111), ['some']),
            ['expired' => true, 'expire_at' => 1111, 'info' => '["some"]']
        ];
    }
}
