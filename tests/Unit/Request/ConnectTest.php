<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Request;

use Testo\Test;
use Testo\Assert;
use Testo\Data\DataProvider;
use Testo\Lifecycle\BeforeTest;
use Google\Protobuf\Internal\MapField;
use Google\Protobuf\RepeatedField;
use RoadRunner\Centrifugal\Proxy\DTO\V1\BoolValue;
use RoadRunner\Centrifugal\Proxy\DTO\V1\ConnectResponse as ConnectResponseDTO;
use RoadRunner\Centrifugal\Proxy\DTO\V1\ConnectResult;
use RoadRunner\Centrifugal\Proxy\DTO\V1\SubscribeOptionOverride;
use RoadRunner\Centrifugal\Proxy\DTO\V1\SubscribeOptions;
use RoadRunner\Centrifugo\Payload\ConnectResponse;
use RoadRunner\Centrifugo\Payload\Override;
use RoadRunner\Centrifugo\Payload\SubscribeOption;
use RoadRunner\Centrifugo\Request\Connect;
use RoadRunner\Centrifugo\Tests\Unit\TestCase;
use Spiral\RoadRunner\Payload;
use Spiral\RoadRunner\WorkerInterface;

#[Test]
final class ConnectTest extends TestCase
{
    private Connect $connect;

    public static function mapResponseDataProvider(): \Traversable
    {
        yield [new ConnectResponse(), [
            'user' => '',
            'expire_at' => 0,
            'data' => '',
            'info' => '',
            'meta' => '',
            'channels' => new RepeatedField(9),
            'subs' => new MapField(9, 11, SubscribeOptions::class),
        ]];
        yield [new ConnectResponse('some-user'), [
            'user' => 'some-user',
            'expire_at' => 0,
            'data' => '',
            'info' => '',
            'meta' => '',
            'channels' => new RepeatedField(9),
            'subs' => new MapField(9, 11, SubscribeOptions::class),
        ]];
        yield [new ConnectResponse(expireAt: 11111), [
            'user' => '',
            'expire_at' => 11111,
            'data' => '',
            'info' => '',
            'meta' => '',
            'channels' => new RepeatedField(9),
            'subs' => new MapField(9, 11, SubscribeOptions::class),
        ]];
        yield [new ConnectResponse(data: ['foo' => 'bar']), [
            'user' => '',
            'expire_at' => 0,
            'data' => '{"foo":"bar"}',
            'info' => '',
            'meta' => '',
            'channels' => new RepeatedField(9),
            'subs' => new MapField(9, 11, SubscribeOptions::class),
        ]];
        yield [new ConnectResponse(info: ['foo' => 'bar']), [
            'user' => '',
            'expire_at' => 0,
            'data' => '',
            'info' => '{"foo":"bar"}',
            'meta' => '',
            'channels' => new RepeatedField(9),
            'subs' => new MapField(9, 11, SubscribeOptions::class),
        ]];
        yield [new ConnectResponse(meta: ['foo' => 'bar']), [
            'user' => '',
            'expire_at' => 0,
            'data' => '',
            'info' => '',
            'meta' => '{"foo":"bar"}',
            'channels' => new RepeatedField(9),
            'subs' => new MapField(9, 11, SubscribeOptions::class),
        ]];

        $channels = new RepeatedField(9);
        $channels[] = 'foo';
        $channels[] = 'bar';
        yield [new ConnectResponse(channels: ['foo', 'bar']), [
            'user' => '',
            'expire_at' => 0,
            'data' => '',
            'info' => '',
            'meta' => '',
            'channels' => $channels,
            'subs' => new MapField(9, 11, SubscribeOptions::class),
        ]];

        $subs = new MapField(9, 11, SubscribeOptions::class);
        $subs['foo'] = new SubscribeOptions();
        $subs['bar'] = new SubscribeOptions();
        $subs['bar']->setExpireAt(11111);
        $subs['bar']->setData(json_encode(['foo' => 'bar']));
        $subs['bar']->setInfo(json_encode(['foo', 'bar']));
        $subs['bar']->setOverride(
            (new SubscribeOptionOverride())
                ->setPresence(new BoolValue(['value' => true]))
                ->setJoinLeave(new BoolValue(['value' => true]))
                ->setForcePushJoinLeave(new BoolValue(['value' => true]))
                ->setForcePositioning(new BoolValue(['value' => true]))
                ->setForceRecovery(new BoolValue(['value' => true])),
        );
        yield [
            new ConnectResponse(subscriptions: [
                'foo' => new SubscribeOption(),
                'bar' => new SubscribeOption(
                    11111,
                    ['foo', 'bar'],
                    ['foo' => 'bar'],
                    new Override(true, true, true, true, true),
                ),
            ]),
            [
                'user' => '',
                'expire_at' => 0,
                'data' => '',
                'info' => '',
                'meta' => '',
                'channels' => new RepeatedField(9),
                'subs' => $subs,
            ],
        ];
    }

    public static function mapSubscriptionsDataProvider(): \Traversable
    {
        yield [new SubscribeOption(), ['expire_at' => 0, 'data' => '', 'info' => '', 'override' => null]];
        yield [new SubscribeOption(222), ['expire_at' => 222, 'data' => '', 'info' => '', 'override' => null]];
        yield [
            new SubscribeOption((new \DateTimeImmutable())->setTimestamp(222)),
            ['expire_at' => 222, 'data' => '', 'info' => '', 'override' => null],
        ];
        yield [
            new SubscribeOption(data: ['foo' => 'bar']),
            ['expire_at' => 0, 'data' => '{"foo":"bar"}', 'info' => '', 'override' => null],
        ];
        yield [
            new SubscribeOption(info: ['foo' => 'bar']),
            ['expire_at' => 0, 'data' => '', 'info' => '{"foo":"bar"}', 'override' => null],
        ];
        yield [
            new SubscribeOption(override: new Override()),
            ['expire_at' => 0, 'data' => '', 'info' => '', 'override' => new SubscribeOptionOverride()],
        ];
        yield [
            new SubscribeOption(override: new Override(true)),
            [
                'expire_at' => 0,
                'data' => '',
                'info' => '',
                'override' => new SubscribeOptionOverride(['presence' => new BoolValue(['value' => true])]),
            ],
        ];
        yield [
            new SubscribeOption(override: new Override(joinLeave: true)),
            [
                'expire_at' => 0,
                'data' => '',
                'info' => '',
                'override' => new SubscribeOptionOverride(['join_leave' => new BoolValue(['value' => true])]),
            ],
        ];
        yield [
            new SubscribeOption(override: new Override(forcePushJoinLeave: true)),
            [
                'expire_at' => 0,
                'data' => '',
                'info' => '',
                'override' => new SubscribeOptionOverride(['force_push_join_leave' => new BoolValue(['value' => true])]),
            ],
        ];
        yield [
            new SubscribeOption(override: new Override(forcePositioning: true)),
            [
                'expire_at' => 0,
                'data' => '',
                'info' => '',
                'override' => new SubscribeOptionOverride(['force_positioning' => new BoolValue(['value' => true])]),
            ],
        ];
        yield [
            new SubscribeOption(override: new Override(forceRecovery: true)),
            [
                'expire_at' => 0,
                'data' => '',
                'info' => '',
                'override' => new SubscribeOptionOverride(['force_recovery' => new BoolValue(['value' => true])]),
            ],
        ];
        yield [
            new SubscribeOption(override: new Override(true, true, true, true, true)),
            [
                'expire_at' => 0,
                'data' => '',
                'info' => '',
                'override' => new SubscribeOptionOverride([
                    'presence' => new BoolValue(['value' => true]),
                    'join_leave' => new BoolValue(['value' => true]),
                    'force_push_join_leave' => new BoolValue(['value' => true]),
                    'force_positioning' => new BoolValue(['value' => true]),
                    'force_recovery' => new BoolValue(['value' => true]),
                ]),
            ],
        ];
        yield [
            new SubscribeOption(111, ['some'], ['other'], new Override(true, true, true, true, true)),
            [
                'expire_at' => 111,
                'data' => '["other"]',
                'info' => '["some"]',
                'override' => new SubscribeOptionOverride([
                    'presence' => new BoolValue(['value' => true]),
                    'join_leave' => new BoolValue(['value' => true]),
                    'force_push_join_leave' => new BoolValue(['value' => true]),
                    'force_positioning' => new BoolValue(['value' => true]),
                    'force_recovery' => new BoolValue(['value' => true]),
                ]),
            ],
        ];
    }

    public static function mapSubscribeOptionDataProvider(): \Traversable
    {
        yield [new Override(), [
            'presence' => null,
            'join_leave' => null,
            'force_push_join_leave' => null,
            'force_positioning' => null,
            'force_recovery' => null,
        ]];
        yield [new Override(true), [
            'presence' => true,
            'join_leave' => null,
            'force_push_join_leave' => null,
            'force_positioning' => null,
            'force_recovery' => null,
        ]];
        yield [new Override(joinLeave: true), [
            'presence' => null,
            'join_leave' => true,
            'force_push_join_leave' => null,
            'force_positioning' => null,
            'force_recovery' => null,
        ]];
        yield [new Override(forcePushJoinLeave: true), [
            'presence' => null,
            'join_leave' => null,
            'force_push_join_leave' => true,
            'force_positioning' => null,
            'force_recovery' => null,
        ]];
        yield [new Override(forcePositioning: true), [
            'presence' => null,
            'join_leave' => null,
            'force_push_join_leave' => null,
            'force_positioning' => true,
            'force_recovery' => null,
        ]];
        yield [new Override(forceRecovery: true), [
            'presence' => null,
            'join_leave' => null,
            'force_push_join_leave' => null,
            'force_positioning' => null,
            'force_recovery' => true,
        ]];
        yield [new Override(true, true, true, true, true), [
            'presence' => true,
            'join_leave' => true,
            'force_push_join_leave' => true,
            'force_positioning' => true,
            'force_recovery' => true,
        ]];
    }

    public function testRespond(): void
    {
        $worker = $this->createWorker(function (Payload $payload) {
            Assert::equals($payload, new Payload((new ConnectResponseDTO(['result' => new ConnectResult()]))->serializeToString()));
        });

        $connect = new Connect($worker, '', '', '', '', [], '', '', [], []);

        $connect->respond(new ConnectResponse());
    }

    public function testGetResponseObject(): void
    {
        $ref = new \ReflectionMethod($this->connect, 'getResponseObject');

        Assert::instanceOf($ref->invoke($this->connect), ConnectResponseDTO::class);
    }

    public function testParseExpiresAt(): void
    {
        Assert::same($this->connect->parseExpiresAt(1111), 1111);
        Assert::same($this->connect->parseExpiresAt((new \DateTimeImmutable())->setTimestamp(1111)), 1111);
    }

    #[DataProvider('mapResponseDataProvider')]
    public function testMapResponse(ConnectResponse $response, array $expected): void
    {
        $ref = new \ReflectionMethod($this->connect, 'mapResponse');

        /** @var ConnectResult $dto */
        $dto = $ref->invoke($this->connect, $response);

        Assert::same($dto->getUser(), $expected['user']);
        Assert::same($dto->getExpireAt(), $expected['expire_at']);
        Assert::same($dto->getData(), $expected['data']);
        Assert::same($dto->getInfo(), $expected['info']);
        Assert::same($dto->getMeta(), $expected['meta']);
        Assert::equals($dto->getChannels(), $expected['channels']);
        Assert::equals($dto->getSubs(), $expected['subs']);
    }

    #[DataProvider('mapSubscriptionsDataProvider')]
    public function testMapSubscriptions(SubscribeOption $options, array $expected): void
    {
        $ref = new \ReflectionMethod($this->connect, 'mapSubscriptions');

        /** @var array<non-empty-string, SubscribeOptions> $subs */
        $subs = $ref->invoke($this->connect, ['a' => $options]);

        /** @var SubscribeOptions $mapped */
        $mapped = $subs['a'];

        Assert::same($mapped->getExpireAt(), $expected['expire_at']);
        Assert::same($mapped->getData(), $expected['data']);
        Assert::same($mapped->getInfo(), $expected['info']);
        Assert::equals($mapped->getOverride(), $expected['override']);
    }

    #[DataProvider('mapSubscribeOptionDataProvider')]
    public function testMapSubscribeOption(Override $override, array $expected): void
    {
        $override = $this->connect->mapSubscribeOption($override);

        Assert::same($override->getPresence()?->getValue(), $expected['presence']);
        Assert::same($override->getJoinLeave()?->getValue(), $expected['join_leave']);
        Assert::same($override->getForcePushJoinLeave()?->getValue(), $expected['force_push_join_leave']);
        Assert::same($override->getForcePositioning()?->getValue(), $expected['force_positioning']);
        Assert::same($override->getForceRecovery()?->getValue(), $expected['force_recovery']);
    }

    #[BeforeTest]
    protected function setUp(): void
    {
        $this->connect = new Connect(\Mockery::mock(WorkerInterface::class)->shouldIgnoreMissing(), '', '', '', '', [], '', '', [], []);
    }
}
