<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Request;

use Google\Protobuf\Internal\RepeatedField;
use RoadRunner\Centrifugo\DTO\BoolValue;
use RoadRunner\Centrifugo\DTO\SubscribeOptionOverride;
use RoadRunner\Centrifugo\DTO\SubscribeResult;
use RoadRunner\Centrifugo\Payload\Override;
use RoadRunner\Centrifugo\Payload\SubscribeResponse;
use RoadRunner\Centrifugo\Request\Subscribe;
use RoadRunner\Centrifugo\Tests\Unit\TestCase;
use Spiral\RoadRunner\Payload;
use RoadRunner\Centrifugo\DTO\SubscribeResponse as SubscribeResponseDTO;
use Spiral\RoadRunner\WorkerInterface;

final class SubscribeTest extends TestCase
{
    private Subscribe $subscribe;

    protected function setUp(): void
    {
        $this->subscribe = new Subscribe($this->createMock(WorkerInterface::class), '', '', '', '', '', '', '', [], [], []);
    }

    public function testRespond(): void
    {
        $worker = $this->createWorker(function (Payload $payload) {
            $this->assertEquals(
                new Payload((new SubscribeResponseDTO(['result' => new SubscribeResult()]))->serializeToString()),
                $payload
            );
        });

        $refresh = new Subscribe($worker, '', '', '', '', '',  '', '', [], [], []);

        $refresh->respond(new SubscribeResponse());
    }

    public function testGetResponseObject(): void
    {
        $ref = new \ReflectionMethod($this->subscribe, 'getResponseObject');

        $this->assertInstanceOf(SubscribeResponseDTO::class, $ref->invoke($this->subscribe));
    }

    /**
     * @dataProvider mapResponseDataProvider
     */
    public function testMapResponse(SubscribeResponse $response, array $expected): void
    {
        $ref = new \ReflectionMethod($this->subscribe, 'mapResponse');

        /** @var SubscribeResult $dto */
        $dto = $ref->invoke($this->subscribe, $response);

        $this->assertSame($expected['info'], $dto->getInfo());
        $this->assertSame($expected['data'], $dto->getData());
        $this->assertEquals($expected['allow'], $dto->getAllow());
        $this->assertEquals($expected['override'], $dto->getOverride());
    }

    public function mapResponseDataProvider(): \Traversable
    {
        yield [
            new SubscribeResponse(),
            ['info' => '', 'data' => '', 'allow' => new RepeatedField(9), 'override' => null]
        ];

        yield [
            new SubscribeResponse(['some']),
            ['info' => '["some"]', 'data' => '', 'allow' => new RepeatedField(9), 'override' => null]
        ];

        yield [
            new SubscribeResponse(data: ['some']),
            ['info' => '', 'data' => '["some"]', 'allow' => new RepeatedField(9), 'override' => null]
        ];

        $allow = new RepeatedField(9);
        $allow[] = 'some';
        $allow[] = 'other';
        yield [
            new SubscribeResponse(allow: ['some', 'other']),
            ['info' => '', 'data' => '', 'allow' => $allow, 'override' => null]
        ];

        yield [
            new SubscribeResponse(override: new Override(true, true, true, true, true)),
            ['info' => '', 'data' => '', 'allow' => new RepeatedField(9), 'override' => (new SubscribeOptionOverride())
                ->setPresence(new BoolValue(['value' => true]))
                ->setJoinLeave(new BoolValue(['value' => true]))
                ->setForcePushJoinLeave(new BoolValue(['value' => true]))
                ->setForcePositioning(new BoolValue(['value' => true]))
                ->setForceRecovery(new BoolValue(['value' => true]))
            ]
        ];

        $allow = new RepeatedField(9);
        $allow[] = 'some';
        $allow[] = 'other';
        yield [
            new SubscribeResponse(
                ['foo'],
                ['bar'],
                ['some', 'other'],
                new Override(true, true, true, true, true)
            ),
            ['info' => '["foo"]', 'data' => '["bar"]', 'allow' => $allow, 'override' => (new SubscribeOptionOverride())
                ->setPresence(new BoolValue(['value' => true]))
                ->setJoinLeave(new BoolValue(['value' => true]))
                ->setForcePushJoinLeave(new BoolValue(['value' => true]))
                ->setForcePositioning(new BoolValue(['value' => true]))
                ->setForceRecovery(new BoolValue(['value' => true]))
            ]
        ];
    }
}
