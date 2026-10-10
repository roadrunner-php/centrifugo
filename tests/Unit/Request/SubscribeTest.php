<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Request;

use Google\Protobuf\RepeatedField;
use RoadRunner\Centrifugal\Proxy\DTO\V1\BoolValue;
use RoadRunner\Centrifugal\Proxy\DTO\V1\SubscribeOptionOverride;
use RoadRunner\Centrifugal\Proxy\DTO\V1\SubscribeResponse as SubscribeResponseDTO;
use RoadRunner\Centrifugal\Proxy\DTO\V1\SubscribeResult;
use RoadRunner\Centrifugo\Payload\Override;
use RoadRunner\Centrifugo\Payload\SubscribeResponse;
use RoadRunner\Centrifugo\Request\Subscribe;
use RoadRunner\Centrifugo\Tests\Unit\TestCase;
use Spiral\RoadRunner\Payload;
use Spiral\RoadRunner\WorkerInterface;
use Testo\Assert;
use Testo\Data\DataProvider;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

#[Test]
final class SubscribeTest extends TestCase
{
    private Subscribe $subscribe;

    public static function mapResponseDataProvider(): \Traversable
    {
        yield [
            new SubscribeResponse(),
            ['info' => '', 'data' => '', 'allow' => new RepeatedField(9), 'override' => null],
        ];

        yield [
            new SubscribeResponse(['some']),
            ['info' => '["some"]', 'data' => '', 'allow' => new RepeatedField(9), 'override' => null],
        ];

        yield [
            new SubscribeResponse(data: ['some']),
            ['info' => '', 'data' => '["some"]', 'allow' => new RepeatedField(9), 'override' => null],
        ];

        $allow = new RepeatedField(9);
        $allow[] = 'some';
        $allow[] = 'other';
        yield [
            new SubscribeResponse(allow: ['some', 'other']),
            ['info' => '', 'data' => '', 'allow' => $allow, 'override' => null],
        ];

        yield [
            new SubscribeResponse(override: new Override(true, true, true, true, true)),
            ['info' => '', 'data' => '', 'allow' => new RepeatedField(9), 'override' => (new SubscribeOptionOverride())
                ->setPresence(new BoolValue(['value' => true]))
                ->setJoinLeave(new BoolValue(['value' => true]))
                ->setForcePushJoinLeave(new BoolValue(['value' => true]))
                ->setForcePositioning(new BoolValue(['value' => true]))
                ->setForceRecovery(new BoolValue(['value' => true])),
            ],
        ];

        $allow = new RepeatedField(9);
        $allow[] = 'some';
        $allow[] = 'other';
        yield [
            new SubscribeResponse(
                ['foo'],
                ['bar'],
                ['some', 'other'],
                new Override(true, true, true, true, true),
            ),
            ['info' => '["foo"]', 'data' => '["bar"]', 'allow' => $allow, 'override' => (new SubscribeOptionOverride())
                ->setPresence(new BoolValue(['value' => true]))
                ->setJoinLeave(new BoolValue(['value' => true]))
                ->setForcePushJoinLeave(new BoolValue(['value' => true]))
                ->setForcePositioning(new BoolValue(['value' => true]))
                ->setForceRecovery(new BoolValue(['value' => true])),
            ],
        ];
    }

    public function testRespond(): void
    {
        $worker = $this->createWorker(function (Payload $payload) {
            Assert::equals($payload, new Payload((new SubscribeResponseDTO(['result' => new SubscribeResult()]))->serializeToString()));
        });

        $refresh = new Subscribe($worker, '', '', '', '', '', '', '', [], [], []);

        $refresh->respond(new SubscribeResponse());
    }

    public function testGetResponseObject(): void
    {
        $ref = new \ReflectionMethod($this->subscribe, 'getResponseObject');

        Assert::instanceOf($ref->invoke($this->subscribe), SubscribeResponseDTO::class);
    }

    #[DataProvider('mapResponseDataProvider')]
    public function testMapResponse(SubscribeResponse $response, array $expected): void
    {
        $ref = new \ReflectionMethod($this->subscribe, 'mapResponse');

        /** @var SubscribeResult $dto */
        $dto = $ref->invoke($this->subscribe, $response);

        Assert::same($dto->getInfo(), $expected['info']);
        Assert::same($dto->getData(), $expected['data']);
        Assert::equals($dto->getAllow(), $expected['allow']);
        Assert::equals($dto->getOverride(), $expected['override']);
    }

    #[BeforeTest]
    protected function setUp(): void
    {
        $this->subscribe = new Subscribe(\Mockery::mock(WorkerInterface::class)->shouldIgnoreMissing(), '', '', '', '', '', '', '', [], [], []);
    }
}
