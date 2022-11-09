<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Request;

use Google\Protobuf\Internal\MapField;
use Google\Protobuf\Internal\RepeatedField;
use RoadRunner\Centrifugo\DTO\BoolValue;
use RoadRunner\Centrifugo\DTO\ConnectResponse as ConnectResponseDTO;
use RoadRunner\Centrifugo\DTO\ConnectResult;
use RoadRunner\Centrifugo\DTO\SubscribeOptionOverride;
use RoadRunner\Centrifugo\DTO\SubscribeOptions;
use RoadRunner\Centrifugo\Payload\ConnectResponse;
use RoadRunner\Centrifugo\Payload\Override;
use RoadRunner\Centrifugo\Payload\SubscribeOption;
use RoadRunner\Centrifugo\Request\Connect;
use RoadRunner\Centrifugo\Tests\Unit\TestCase;
use Spiral\RoadRunner\Payload;
use Spiral\RoadRunner\WorkerInterface;

final class ConnectTest extends TestCase
{
    private Connect $connect;

    protected function setUp(): void
    {
        $this->connect = new Connect($this->createMock(WorkerInterface::class), '', '', '', '', [], '', '', [], []);
    }

    public function testRespond(): void
    {
        $worker = $this->createWorker(function (Payload $payload) {
            $this->assertEquals(
                new Payload((new ConnectResponseDTO(['result' => new ConnectResult()]))->serializeToString()),
                $payload
            );
        });

        $connect = new Connect($worker, '', '', '', '', [], '', '', [], []);

        $connect->respond(new ConnectResponse());
    }

    public function testGetResponseObject(): void
    {
        $ref = new \ReflectionMethod($this->connect, 'getResponseObject');

        $this->assertInstanceOf(ConnectResponseDTO::class, $ref->invoke($this->connect));
    }

    public function testParseExpiresAt(): void
    {
        $this->assertSame(1111, $this->connect->parseExpiresAt(1111));
        $this->assertSame(1111, $this->connect->parseExpiresAt((new \DateTimeImmutable())->setTimestamp(1111)));
    }

    /**
     * @dataProvider mapResponseDataProvider
     */
    public function testMapResponse(ConnectResponse $response, array $expected): void
    {
        $ref = new \ReflectionMethod($this->connect, 'mapResponse');

        /** @var ConnectResult $dto */
        $dto = $ref->invoke($this->connect, $response);

        $this->assertSame($expected['user'], $dto->getUser());
        $this->assertSame($expected['expire_at'], $dto->getExpireAt());
        $this->assertSame($expected['data'], $dto->getData());
        $this->assertSame($expected['info'], $dto->getInfo());
        $this->assertSame($expected['meta'], $dto->getMeta());
        $this->assertEquals($expected['channels'], $dto->getChannels());
        $this->assertEquals($expected['subs'], $dto->getSubs());
    }

    /**
     * @dataProvider mapSubscriptionsDataProvider
     */
    public function testMapSubscriptions(SubscribeOption $options, array $expected): void
    {
        $ref = new \ReflectionMethod($this->connect, 'mapSubscriptions');

        /** @var array<non-empty-string, SubscribeOptions> $subs */
        $subs = $ref->invoke($this->connect, ['a' => $options]);

        /** @var SubscribeOptions $mapped */
        $mapped = $subs['a'];

        $this->assertSame($expected['expire_at'], $mapped->getExpireAt());
        $this->assertSame($expected['data'], $mapped->getData());
        $this->assertSame($expected['info'], $mapped->getInfo());
        $this->assertEquals($expected['override'], $mapped->getOverride());
    }

    /**
     * @dataProvider mapSubscribeOptionDataProvider
     */
    public function testMapSubscribeOption(Override $override, array $expected): void
    {
        $override = $this->connect->mapSubscribeOption($override);

        $this->assertSame($expected['presence'], $override->getPresence()?->getValue());
        $this->assertSame($expected['join_leave'], $override->getJoinLeave()?->getValue());
        $this->assertSame($expected['force_push_join_leave'], $override->getForcePushJoinLeave()?->getValue());
        $this->assertSame($expected['force_positioning'], $override->getForcePositioning()?->getValue());
        $this->assertSame($expected['force_recovery'], $override->getForceRecovery()?->getValue());
    }

    public function mapResponseDataProvider(): \Traversable
    {
        yield [new ConnectResponse(), [
            'user' => '',
            'expire_at' => 0,
            'data' => '',
            'info' => '',
            'meta' => '',
            'channels' => new RepeatedField(9),
            'subs' => new MapField(9, 11, SubscribeOptions::class)
        ]];
        yield [new ConnectResponse('some-user'), [
            'user' => 'some-user',
            'expire_at' => 0,
            'data' => '',
            'info' => '',
            'meta' => '',
            'channels' => new RepeatedField(9),
            'subs' => new MapField(9, 11, SubscribeOptions::class)
        ]];
        yield [new ConnectResponse(expireAt: 11111), [
            'user' => '',
            'expire_at' => 11111,
            'data' => '',
            'info' => '',
            'meta' => '',
            'channels' => new RepeatedField(9),
            'subs' => new MapField(9, 11, SubscribeOptions::class)
        ]];
        yield [new ConnectResponse(data: ['foo' => 'bar']), [
            'user' => '',
            'expire_at' => 0,
            'data' => '{"foo":"bar"}',
            'info' => '',
            'meta' => '',
            'channels' => new RepeatedField(9),
            'subs' => new MapField(9, 11, SubscribeOptions::class)
        ]];
        yield [new ConnectResponse(info: ['foo' => 'bar']), [
            'user' => '',
            'expire_at' => 0,
            'data' => '',
            'info' => '{"foo":"bar"}',
            'meta' => '',
            'channels' => new RepeatedField(9),
            'subs' => new MapField(9, 11, SubscribeOptions::class)
        ]];
        yield [new ConnectResponse(meta: ['foo' => 'bar']), [
            'user' => '',
            'expire_at' => 0,
            'data' => '',
            'info' => '',
            'meta' => '{"foo":"bar"}',
            'channels' => new RepeatedField(9),
            'subs' => new MapField(9, 11, SubscribeOptions::class)
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
            'subs' => new MapField(9, 11, SubscribeOptions::class)
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
                ->setForceRecovery(new BoolValue(['value' => true]))
        );
        yield [
            new ConnectResponse(subscriptions: [
                'foo' => new SubscribeOption(),
                'bar' => new SubscribeOption(
                    11111,
                    ['foo', 'bar'],
                    ['foo' => 'bar'],
                    new Override(true, true, true, true, true)
                )
            ]),
            [
                'user' => '',
                'expire_at' => 0,
                'data' => '',
                'info' => '',
                'meta' => '',
                'channels' => new RepeatedField(9),
                'subs' => $subs
            ]
        ];
    }

    public function mapSubscriptionsDataProvider(): \Traversable
    {
        yield [new SubscribeOption(), ['expire_at' => 0, 'data' => '', 'info' => '', 'override' => null]];
        yield [new SubscribeOption(222), ['expire_at' => 222, 'data' => '', 'info' => '', 'override' => null]];
        yield [
            new SubscribeOption((new \DateTimeImmutable())->setTimestamp(222)),
            ['expire_at' => 222, 'data' => '', 'info' => '', 'override' => null]
        ];
        yield [
            new SubscribeOption(data: ['foo' => 'bar']),
            ['expire_at' => 0, 'data' => '{"foo":"bar"}', 'info' => '', 'override' => null]
        ];
        yield [
            new SubscribeOption(info: ['foo' => 'bar']),
            ['expire_at' => 0, 'data' => '', 'info' => '{"foo":"bar"}', 'override' => null]
        ];
        yield [
            new SubscribeOption(override: new Override()),
            ['expire_at' => 0, 'data' => '', 'info' => '', 'override' => new SubscribeOptionOverride()]
        ];
        yield [
            new SubscribeOption(override: new Override(true)),
            [
                'expire_at' => 0,
                'data' => '',
                'info' => '',
                'override' => new SubscribeOptionOverride(['presence' => new BoolValue(['value' => true])])
            ]
        ];
        yield [
            new SubscribeOption(override: new Override(joinLeave: true)),
            [
                'expire_at' => 0,
                'data' => '',
                'info' => '',
                'override' => new SubscribeOptionOverride(['join_leave' => new BoolValue(['value' => true])])
            ]
        ];
        yield [
            new SubscribeOption(override: new Override(forcePushJoinLeave: true)),
            [
                'expire_at' => 0,
                'data' => '',
                'info' => '',
                'override' => new SubscribeOptionOverride(['force_push_join_leave' => new BoolValue(['value' => true])])
            ]
        ];
        yield [
            new SubscribeOption(override: new Override(forcePositioning: true)),
            [
                'expire_at' => 0,
                'data' => '',
                'info' => '',
                'override' => new SubscribeOptionOverride(['force_positioning' => new BoolValue(['value' => true])])
            ]
        ];
        yield [
            new SubscribeOption(override: new Override(forceRecovery: true)),
            [
                'expire_at' => 0,
                'data' => '',
                'info' => '',
                'override' => new SubscribeOptionOverride(['force_recovery' => new BoolValue(['value' => true])])
            ]
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
                    'force_recovery' => new BoolValue(['value' => true])
                ])
            ]
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
                    'force_recovery' => new BoolValue(['value' => true])
                ])
            ]
        ];
    }

    public function mapSubscribeOptionDataProvider(): \Traversable
    {
        yield [new Override(), [
            'presence' => null,
            'join_leave' => null,
            'force_push_join_leave' => null,
            'force_positioning' => null,
            'force_recovery' => null
        ]];
        yield [new Override(true), [
            'presence' => true,
            'join_leave' => null,
            'force_push_join_leave' => null,
            'force_positioning' => null,
            'force_recovery' => null
        ]];
        yield [new Override(joinLeave: true), [
            'presence' => null,
            'join_leave' => true,
            'force_push_join_leave' => null,
            'force_positioning' => null,
            'force_recovery' => null
        ]];
        yield [new Override(forcePushJoinLeave: true), [
            'presence' => null,
            'join_leave' => null,
            'force_push_join_leave' => true,
            'force_positioning' => null,
            'force_recovery' => null
        ]];
        yield [new Override(forcePositioning: true), [
            'presence' => null,
            'join_leave' => null,
            'force_push_join_leave' => null,
            'force_positioning' => true,
            'force_recovery' => null
        ]];
        yield [new Override(forceRecovery: true), [
            'presence' => null,
            'join_leave' => null,
            'force_push_join_leave' => null,
            'force_positioning' => null,
            'force_recovery' => true
        ]];
        yield [new Override(true, true, true, true, true), [
            'presence' => true,
            'join_leave' => true,
            'force_push_join_leave' => true,
            'force_positioning' => true,
            'force_recovery' => true
        ]];
    }
}
