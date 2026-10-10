<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit;

use Google\Protobuf\Internal\Message;
use RoadRunner\Centrifugal\API\DTO\V1 as DTO;
use RoadRunner\Centrifugo\Exception\CentrifugoApiResponseException;
use RoadRunner\Centrifugo\Payload\Disconnect;
use RoadRunner\Centrifugo\RPCCentrifugoApi;
use RoadRunner\Centrifugo\Tests\Unit\Stub\CentrifugeRpcRelay;
use Spiral\Goridge\RPC\RPC;
use Testo\Assert;
use Testo\Expect;
use Testo\Test;

/**
 * Drives {@see RPCCentrifugoApi} through a real Goridge RPC client and protobuf codec, so every request and
 * response crosses the wire as serialized `roadrunner/api-dto` messages.
 */
#[Test]
final class RPCCentrifugoApiWireTest
{
    public function testPublish(): void
    {
        $relay = new CentrifugeRpcRelay(DTO\PublishRequest::class, new DTO\PublishResponse([
            'result' => new DTO\PublishResult(['offset' => 7, 'epoch' => 'abc']),
        ]));

        self::api($relay)->publish('news', '{"text":"hi"}', false, ['k' => 'v']);

        $request = self::request($relay, 'centrifuge.Publish', DTO\PublishRequest::class);
        Assert::same($request->getChannel(), 'news');
        Assert::same($request->getData(), '{"text":"hi"}');
        Assert::same($request->getSkipHistory(), false);
        Assert::same(\iterator_to_array($request->getTags()->getIterator()), ['k' => 'v']);
    }

    public function testBroadcast(): void
    {
        $relay = new CentrifugeRpcRelay(DTO\BroadcastRequest::class, new DTO\BroadcastResponse([
            'result' => new DTO\BroadcastResult(['responses' => [new DTO\PublishResponse()]]),
        ]));

        self::api($relay)->broadcast(['a', 'b'], '{"text":"hi"}', true, ['k' => 'v']);

        $request = self::request($relay, 'centrifuge.Broadcast', DTO\BroadcastRequest::class);
        Assert::same(\iterator_to_array($request->getChannels()), ['a', 'b']);
        Assert::same($request->getData(), '{"text":"hi"}');
        Assert::same($request->getSkipHistory(), true);
        Assert::same(\iterator_to_array($request->getTags()->getIterator()), ['k' => 'v']);
    }

    public function testRefresh(): void
    {
        $relay = new CentrifugeRpcRelay(DTO\RefreshRequest::class, new DTO\RefreshResponse([
            'result' => new DTO\RefreshResult(),
        ]));

        self::api($relay)->refresh('user', 'client', 'session', true, new \DateTimeImmutable('@1700000000'));

        $request = self::request($relay, 'centrifuge.Refresh', DTO\RefreshRequest::class);
        Assert::same($request->getUser(), 'user');
        Assert::same($request->getClient(), 'client');
        Assert::same($request->getSession(), 'session');
        Assert::same($request->getExpired(), true);
        Assert::same($request->getExpireAt(), 1700000000);
    }

    public function testSubscribe(): void
    {
        $relay = new CentrifugeRpcRelay(DTO\SubscribeRequest::class, new DTO\SubscribeResponse([
            'result' => new DTO\SubscribeResult(),
        ]));

        self::api($relay)->subscribe(
            channel: 'news',
            user: 'user',
            expireAt: new \DateTimeImmutable('@1700000000'),
            info: ['role' => 'admin'],
            client: 'client',
            data: ['greeting' => 'hi'],
            session: 'session',
        );

        $request = self::request($relay, 'centrifuge.Subscribe', DTO\SubscribeRequest::class);
        Assert::same($request->getChannel(), 'news');
        Assert::same($request->getUser(), 'user');
        Assert::same($request->getExpireAt(), 1700000000);
        Assert::same($request->getInfo(), '{"role":"admin"}');
        Assert::same($request->getData(), '{"greeting":"hi"}');
        Assert::same($request->getClient(), 'client');
        Assert::same($request->getSession(), 'session');
    }

    public function testUnsubscribe(): void
    {
        $relay = new CentrifugeRpcRelay(DTO\UnsubscribeRequest::class, new DTO\UnsubscribeResponse([
            'result' => new DTO\UnsubscribeResult(),
        ]));

        self::api($relay)->unsubscribe('news', 'user', 'client', 'session');

        $request = self::request($relay, 'centrifuge.Unsubscribe', DTO\UnsubscribeRequest::class);
        Assert::same($request->getChannel(), 'news');
        Assert::same($request->getUser(), 'user');
        Assert::same($request->getClient(), 'client');
        Assert::same($request->getSession(), 'session');
    }

    public function testDisconnect(): void
    {
        $relay = new CentrifugeRpcRelay(DTO\DisconnectRequest::class, new DTO\DisconnectResponse([
            'result' => new DTO\DisconnectResult(),
        ]));

        self::api($relay)->disconnect('user', 'client', ['keep'], 'session', new Disconnect(4000, 'bye'));

        $request = self::request($relay, 'centrifuge.Disconnect', DTO\DisconnectRequest::class);
        Assert::same($request->getUser(), 'user');
        Assert::same($request->getClient(), 'client');
        Assert::same(\iterator_to_array($request->getWhitelist()), ['keep']);
        Assert::same($request->getSession(), 'session');
        Assert::same($request->getDisconnect()?->getCode(), 4000);
        Assert::same($request->getDisconnect()?->getReason(), 'bye');
    }

    public function testPresence(): void
    {
        $relay = new CentrifugeRpcRelay(DTO\PresenceRequest::class, new DTO\PresenceResponse([
            'result' => new DTO\PresenceResult([
                'presence' => [
                    'c1' => new DTO\ClientInfo([
                        'client' => 'c1',
                        'user' => 'u1',
                        'conn_info' => '{"name":"Alice"}',
                        'chan_info' => '{"role":"admin"}',
                    ]),
                ],
            ]),
        ]));

        $presence = self::api($relay)->presence('news');

        $request = self::request($relay, 'centrifuge.Presence', DTO\PresenceRequest::class);
        Assert::same($request->getChannel(), 'news');
        Assert::same($presence, [
            'c1' => [
                'client' => 'c1',
                'user' => 'u1',
                'conn_info' => '{"name":"Alice"}',
                'chan_info' => '{"role":"admin"}',
            ],
        ]);
    }

    public function testPresenceStats(): void
    {
        $relay = new CentrifugeRpcRelay(DTO\PresenceStatsRequest::class, new DTO\PresenceStatsResponse([
            'result' => new DTO\PresenceStatsResult(['num_clients' => 3, 'num_users' => 2]),
        ]));

        $stats = self::api($relay)->presenceStats('news');

        $request = self::request($relay, 'centrifuge.PresenceStats', DTO\PresenceStatsRequest::class);
        Assert::same($request->getChannel(), 'news');
        Assert::same($stats, ['num_clients' => 3, 'num_users' => 2]);
    }

    public function testChannels(): void
    {
        $relay = new CentrifugeRpcRelay(DTO\ChannelsRequest::class, new DTO\ChannelsResponse([
            'result' => new DTO\ChannelsResult([
                'channels' => ['news' => new DTO\ChannelInfo(['num_clients' => 5])],
            ]),
        ]));

        $channels = self::api($relay)->channels('n*');

        $request = self::request($relay, 'centrifuge.Channels', DTO\ChannelsRequest::class);
        Assert::same($request->getPattern(), 'n*');
        Assert::same($channels, ['news' => ['num_clients' => 5]]);
    }

    public function testBlockUser(): void
    {
        $relay = new CentrifugeRpcRelay(DTO\BlockUserRequest::class, new DTO\BlockUserResponse([
            'result' => new DTO\BlockUserResult(),
        ]));

        self::api($relay)->blockUser('user', new \DateTimeImmutable('@1700000000'));

        $request = self::request($relay, 'centrifuge.BlockUser', DTO\BlockUserRequest::class);
        Assert::same($request->getUser(), 'user');
        Assert::same($request->getExpireAt(), 1700000000);
    }

    public function testUnblockUser(): void
    {
        $relay = new CentrifugeRpcRelay(DTO\UnblockUserRequest::class, new DTO\UnblockUserResponse([
            'result' => new DTO\UnblockUserResult(),
        ]));

        self::api($relay)->unblockUser('user');

        $request = self::request($relay, 'centrifuge.UnblockUser', DTO\UnblockUserRequest::class);
        Assert::same($request->getUser(), 'user');
    }

    public function testErrorReply(): void
    {
        Expect::exception(CentrifugoApiResponseException::class)->withMessageContaining('unknown channel')->withCode(102);

        $relay = new CentrifugeRpcRelay(DTO\PublishRequest::class, new DTO\PublishResponse([
            'error' => new DTO\Error(['code' => 102, 'message' => 'unknown channel']),
        ]));

        self::api($relay)->publish('news', '{}');
    }

    private static function api(CentrifugeRpcRelay $relay): RPCCentrifugoApi
    {
        return new RPCCentrifugoApi(new RPC($relay));
    }

    /**
     * @template T of Message
     * @param non-empty-string $method
     * @param class-string<T> $class
     * @return T
     */
    private static function request(CentrifugeRpcRelay $relay, string $method, string $class): Message
    {
        Assert::same($relay->method, $method);
        Assert::instanceOf($relay->request, $class);

        return $relay->request;
    }
}
