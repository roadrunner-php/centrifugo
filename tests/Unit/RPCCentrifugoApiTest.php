<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit;

use Google\Protobuf\Internal\Message;
use Testo\Assert;
use Testo\Skip;
use Testo\Test;
use Testo\Expect;
use Testo\Lifecycle\BeforeTest;
use Mockery as m;
use RoadRunner\Centrifugo\CentrifugoApiInterface;
use RoadRunner\Centrifugo\Exception\CentrifugoApiResponseException;
use RoadRunner\Centrifugo\Payload\Disconnect;
use RoadRunner\Centrifugo\RPCCentrifugoApi;
use RoadRunner\Centrifugal\API\DTO\V1 as DTO;
use Spiral\Goridge\RPC\Codec\ProtobufCodec;
use Spiral\Goridge\RPC\CodecInterface;
use Spiral\Goridge\RPC\RPCInterface;

#[Test]
final class RPCCentrifugoApiTest
{
    private m\MockInterface|RPCInterface $rpc;
    private CentrifugoApiInterface $api;
    private ?Message $request = null;

    public function testPublish(): void
    {
        $this->rpc->shouldReceive('call')
            ->once()
            ->withArgs(
                fn(
                    string $method,
                    DTO\PublishRequest $request,
                    string $responseClass,
                ): bool => $method === 'centrifuge.Publish'
                && $request->getChannel() === 'foo-channel'
                && $request->getData() === \json_encode(['foo' => 'bar'])
                && $request->getSkipHistory() === true
                && self::tagsOf($request) === ['baz', 'baf']
                && $responseClass === DTO\PublishResponse::class,
            )
            ->andReturn(new DTO\PublishResponse());

        $this->api->publish(channel: 'foo-channel', message: \json_encode(['foo' => 'bar']), skipHistory: true, tags: ['baz', 'baf']);
    }

    public function testPublishErrorHandling(): void
    {
        Expect::exception(CentrifugoApiResponseException::class)->withMessageContaining('Error message')->withCode(500);

        $this->rpc->shouldReceive('call')
            ->once()
            ->andReturn(
                new DTO\PublishResponse([
                    'error' => new DTO\Error([
                        'code' => 500,
                        'message' => 'Error message',
                    ]),
                ]),
            );

        $this->api->publish(channel: 'foo-channel', message: \json_encode(['foo' => 'bar']), skipHistory: true, tags: ['baz', 'baf']);
    }

    public function testDisconnectWithDisconnectObject(): void
    {
        $this->rpc->shouldReceive('call')
            ->once()
            ->withArgs(
                fn(
                    string $method,
                    DTO\DisconnectRequest $request,
                    string $responseClass,
                ): bool => $method === 'centrifuge.Unsubscribe'
                && $request->getUser() === 'foo-user'
                && $request->getClient() === 'foo-client'
                && $request->getSession() === 'foo-session'
                && $request->getDisconnect()->getCode() === 400
                && $request->getDisconnect()->getReason() === 'foo-reason'
                && $responseClass === DTO\DisconnectResponse::class,
            )
            ->andReturn(new DTO\DisconnectResponse());

        $this->api->disconnect(
            user: 'foo-user',
            client: 'foo-client',
            session: 'foo-session',
            disconnect: new Disconnect(code: 400, reason: 'foo-reason'),
        );
    }

    public function testDisconnectWithDisconnectObjectAndDeprecatedReconnect(): void
    {
        $this->rpc->shouldReceive('call')
            ->once()
            ->withArgs(
                fn(
                    string $method,
                    DTO\DisconnectRequest $request,
                    string $responseClass,
                ): bool => $method === 'centrifuge.Unsubscribe'
                && $request->getUser() === 'foo-user'
                && $request->getClient() === 'foo-client'
                && $request->getSession() === 'foo-session'
                && $request->getDisconnect()->getCode() === 400
                && $request->getDisconnect()->getReason() === 'foo-reason'
                && $responseClass === DTO\DisconnectResponse::class,
            )
            ->andReturn(new DTO\DisconnectResponse());

        $this->api->disconnect(
            user: 'foo-user',
            client: 'foo-client',
            session: 'foo-session',
            disconnect: new Disconnect(code: 400, reason: 'foo-reason', reconnect: true),
        );
    }

    public function testPublishWithoutTags(): void
    {
        $this->expectCall('centrifuge.Publish', DTO\PublishResponse::class, new DTO\PublishResponse());

        $this->api->publish(channel: 'foo-channel', message: 'message', skipHistory: false);

        Assert::instanceOf($this->request, DTO\PublishRequest::class);
        Assert::same($this->request->getSkipHistory(), false);
        Assert::same(\iterator_to_array($this->request->getTags()->getIterator()), []);
    }

    public function testBroadcast(): void
    {
        $this->expectCall('centrifuge.Broadcast', DTO\BroadcastResponse::class, new DTO\BroadcastResponse());

        $this->api->broadcast(channels: ['foo', 'bar'], message: 'message', skipHistory: false, tags: ['baz' => 'baf']);

        Assert::instanceOf($this->request, DTO\BroadcastRequest::class);
        Assert::same(\iterator_to_array($this->request->getChannels()), ['foo', 'bar']);
        Assert::same($this->request->getData(), 'message');
        Assert::same($this->request->getSkipHistory(), false);
        Assert::same(\iterator_to_array($this->request->getTags()->getIterator()), ['baz' => 'baf']);
    }

    public function testBroadcastWithoutTags(): void
    {
        $this->expectCall('centrifuge.Broadcast', DTO\BroadcastResponse::class, new DTO\BroadcastResponse());

        $this->api->broadcast(channels: ['foo'], message: 'message');

        Assert::instanceOf($this->request, DTO\BroadcastRequest::class);
        Assert::same($this->request->getSkipHistory(), true);
        Assert::same(\iterator_to_array($this->request->getTags()->getIterator()), []);
    }

    public function testRefresh(): void
    {
        $this->expectCall('centrifuge.Refresh', DTO\RefreshResponse::class, new DTO\RefreshResponse());

        $this->api->refresh(
            user: 'foo-user',
            client: 'foo-client',
            session: 'foo-session',
            expired: true,
            expireAt: new \DateTimeImmutable('@1700000000'),
        );

        Assert::instanceOf($this->request, DTO\RefreshRequest::class);
        Assert::same($this->request->getUser(), 'foo-user');
        Assert::same($this->request->getClient(), 'foo-client');
        Assert::same($this->request->getSession(), 'foo-session');
        Assert::same($this->request->getExpired(), true);
        Assert::same($this->request->getExpireAt(), 1700000000);
    }

    public function testRefreshWithDefaults(): void
    {
        $this->expectCall('centrifuge.Refresh', DTO\RefreshResponse::class, new DTO\RefreshResponse());

        $this->api->refresh(user: 'foo-user');

        Assert::instanceOf($this->request, DTO\RefreshRequest::class);
        Assert::same($this->request->getUser(), 'foo-user');
        Assert::same($this->request->getClient(), '');
        Assert::same($this->request->getSession(), '');
        Assert::same($this->request->getExpired(), false);
        Assert::same($this->request->getExpireAt(), 0);
    }

    public function testSubscribe(): void
    {
        $this->expectCall('centrifuge.Subscribe', DTO\SubscribeResponse::class, new DTO\SubscribeResponse());

        $this->api->subscribe(
            channel: 'foo-channel',
            user: 'foo-user',
            expireAt: new \DateTimeImmutable('@1700000000'),
            info: ['foo' => 'bar'],
            client: 'foo-client',
            data: ['baz' => 'baf'],
            session: 'foo-session',
        );

        Assert::instanceOf($this->request, DTO\SubscribeRequest::class);
        Assert::same($this->request->getChannel(), 'foo-channel');
        Assert::same($this->request->getUser(), 'foo-user');
        Assert::same($this->request->getExpireAt(), 1700000000);
        Assert::same($this->request->getInfo(), '{"foo":"bar"}');
        Assert::same($this->request->getClient(), 'foo-client');
        Assert::same($this->request->getData(), '{"baz":"baf"}');
        Assert::same($this->request->getSession(), 'foo-session');
    }

    public function testSubscribeWithDefaults(): void
    {
        $this->expectCall('centrifuge.Subscribe', DTO\SubscribeResponse::class, new DTO\SubscribeResponse());

        $this->api->subscribe(channel: 'foo-channel', user: 'foo-user');

        Assert::instanceOf($this->request, DTO\SubscribeRequest::class);
        Assert::same($this->request->getExpireAt(), 0);
        Assert::same($this->request->getInfo(), '');
        Assert::same($this->request->getClient(), '');
        Assert::same($this->request->getData(), '');
        Assert::same($this->request->getSession(), '');
    }

    public function testUnsubscribe(): void
    {
        $this->expectCall('centrifuge.Unsubscribe', DTO\UnsubscribeResponse::class, new DTO\UnsubscribeResponse());

        $this->api->unsubscribe(channel: 'foo-channel', user: 'foo-user', client: 'foo-client', session: 'foo-session');

        Assert::instanceOf($this->request, DTO\UnsubscribeRequest::class);
        Assert::same($this->request->getChannel(), 'foo-channel');
        Assert::same($this->request->getUser(), 'foo-user');
        Assert::same($this->request->getClient(), 'foo-client');
        Assert::same($this->request->getSession(), 'foo-session');
    }

    public function testUnsubscribeWithDefaults(): void
    {
        $this->expectCall('centrifuge.Unsubscribe', DTO\UnsubscribeResponse::class, new DTO\UnsubscribeResponse());

        $this->api->unsubscribe(channel: 'foo-channel', user: 'foo-user');

        Assert::instanceOf($this->request, DTO\UnsubscribeRequest::class);
        Assert::same($this->request->getClient(), '');
        Assert::same($this->request->getSession(), '');
    }

    /**
     * The RPC method name is not checked here: see {@see self::testDisconnectCallsDisconnectMethod()}.
     */
    public function testDisconnectWithWhitelist(): void
    {
        $this->expectCall(m::any(), DTO\DisconnectResponse::class, new DTO\DisconnectResponse());

        $this->api->disconnect(user: 'foo-user', whitelist: ['client-1', 'client-2']);

        Assert::instanceOf($this->request, DTO\DisconnectRequest::class);
        Assert::same($this->request->getUser(), 'foo-user');
        Assert::same(\iterator_to_array($this->request->getWhitelist()), ['client-1', 'client-2']);
        Assert::null($this->request->getDisconnect());
    }

    #[Skip('Bug: disconnect() calls the centrifuge.Unsubscribe RPC method instead of centrifuge.Disconnect')]
    public function testDisconnectCallsDisconnectMethod(): void
    {
        $this->expectCall('centrifuge.Disconnect', DTO\DisconnectResponse::class, new DTO\DisconnectResponse());

        $this->api->disconnect(user: 'foo-user');

        Assert::instanceOf($this->request, DTO\DisconnectRequest::class);
    }

    public function testPresence(): void
    {
        $this->expectCall('centrifuge.Presence', DTO\PresenceResponse::class, new DTO\PresenceResponse([
            'result' => new DTO\PresenceResult([
                'presence' => [
                    'client-1' => new DTO\ClientInfo([
                        'client' => 'client-1',
                        'user' => 'user-1',
                        'conn_info' => 'conn-1',
                        'chan_info' => 'chan-1',
                    ]),
                    'client-2' => new DTO\ClientInfo(['client' => 'client-2', 'user' => 'user-2']),
                ],
            ]),
        ]));

        $presence = $this->api->presence('foo-channel');
        \ksort($presence);

        Assert::instanceOf($this->request, DTO\PresenceRequest::class);
        Assert::same($this->request->getChannel(), 'foo-channel');
        Assert::same($presence, [
            'client-1' => ['client' => 'client-1', 'user' => 'user-1', 'conn_info' => 'conn-1', 'chan_info' => 'chan-1'],
            'client-2' => ['client' => 'client-2', 'user' => 'user-2', 'conn_info' => '', 'chan_info' => ''],
        ]);
    }

    public function testPresenceWithoutResult(): void
    {
        $this->expectCall('centrifuge.Presence', DTO\PresenceResponse::class, new DTO\PresenceResponse());

        Assert::same($this->api->presence('foo-channel'), []);
    }

    public function testPresenceStats(): void
    {
        $this->expectCall('centrifuge.PresenceStats', DTO\PresenceStatsResponse::class, new DTO\PresenceStatsResponse([
            'result' => new DTO\PresenceStatsResult(['num_clients' => 3, 'num_users' => 2]),
        ]));

        $stats = $this->api->presenceStats('foo-channel');

        Assert::instanceOf($this->request, DTO\PresenceStatsRequest::class);
        Assert::same($this->request->getChannel(), 'foo-channel');
        Assert::same($stats, ['num_clients' => 3, 'num_users' => 2]);
    }

    public function testPresenceStatsWithoutResult(): void
    {
        $this->expectCall('centrifuge.PresenceStats', DTO\PresenceStatsResponse::class, new DTO\PresenceStatsResponse());

        Assert::same($this->api->presenceStats('foo-channel'), ['num_clients' => 0, 'num_users' => 0]);
    }

    public function testChannels(): void
    {
        $this->expectCall('centrifuge.Channels', DTO\ChannelsResponse::class, new DTO\ChannelsResponse([
            'result' => new DTO\ChannelsResult([
                'channels' => [
                    'chat:1' => new DTO\ChannelInfo(['num_clients' => 5]),
                    'chat:2' => new DTO\ChannelInfo(['num_clients' => 1]),
                ],
            ]),
        ]));

        $channels = $this->api->channels('chat:*');
        \ksort($channels);

        Assert::instanceOf($this->request, DTO\ChannelsRequest::class);
        Assert::same($this->request->getPattern(), 'chat:*');
        Assert::same($channels, [
            'chat:1' => ['num_clients' => 5],
            'chat:2' => ['num_clients' => 1],
        ]);
    }

    public function testChannelsWithoutPatternAndResult(): void
    {
        $this->expectCall('centrifuge.Channels', DTO\ChannelsResponse::class, new DTO\ChannelsResponse());

        Assert::same($this->api->channels(), []);
        Assert::instanceOf($this->request, DTO\ChannelsRequest::class);
        Assert::same($this->request->getPattern(), '');
    }

    public function testBlockUser(): void
    {
        $this->expectCall('centrifuge.BlockUser', DTO\BlockUserResponse::class, new DTO\BlockUserResponse());

        $this->api->blockUser('foo-user', new \DateTimeImmutable('@1700000000'));

        Assert::instanceOf($this->request, DTO\BlockUserRequest::class);
        Assert::same($this->request->getUser(), 'foo-user');
        Assert::same($this->request->getExpireAt(), 1700000000);
    }

    public function testBlockUserWithoutExpiration(): void
    {
        $this->expectCall('centrifuge.BlockUser', DTO\BlockUserResponse::class, new DTO\BlockUserResponse());

        $this->api->blockUser('foo-user');

        Assert::instanceOf($this->request, DTO\BlockUserRequest::class);
        Assert::same($this->request->getExpireAt(), 0);
    }

    public function testUnblockUser(): void
    {
        $this->expectCall('centrifuge.UnblockUser', DTO\UnblockUserResponse::class, new DTO\UnblockUserResponse());

        $this->api->unblockUser('foo-user');

        Assert::instanceOf($this->request, DTO\UnblockUserRequest::class);
        Assert::same($this->request->getUser(), 'foo-user');
    }

    public function testErrorResponseOnReadMethod(): void
    {
        Expect::exception(CentrifugoApiResponseException::class)->withMessageContaining('Not found')->withCode(404);

        $this->expectCall('centrifuge.PresenceStats', DTO\PresenceStatsResponse::class, new DTO\PresenceStatsResponse([
            'error' => new DTO\Error(['code' => 404, 'message' => 'Not found']),
        ]));

        $this->api->presenceStats('foo-channel');
    }

    #[BeforeTest]
    protected function setUp(): void
    {
        $this->rpc = m::mock(RPCInterface::class);
        $this->rpc->shouldReceive('withCodec')->once()->withArgs(
            static fn(CodecInterface $codec): bool => $codec instanceof ProtobufCodec,
        )->andReturnSelf();

        $this->api = new RPCCentrifugoApi($this->rpc);
        $this->request = null;
    }

    /**
     * The iteration order of a protobuf `MapField` is an implementation detail of the runtime and differs
     * between releases (google/protobuf 4.33 already yields the entries of a two-element map back to front),
     * so compare the tags by key instead of by iteration order.
     *
     * @return array<array-key, string>
     */
    private static function tagsOf(DTO\PublishRequest $request): array
    {
        $tags = \iterator_to_array($request->getTags()->getIterator());
        \ksort($tags);

        return $tags;
    }

    /**
     * Expects exactly one RPC call and keeps the request message it was given in {@see self::$request}.
     *
     * @param non-empty-string|m\Matcher\MatcherAbstract $method
     * @param class-string<Message> $responseClass
     */
    private function expectCall(string|m\Matcher\MatcherAbstract $method, string $responseClass, Message $response): void
    {
        $this->rpc->shouldReceive('call')
            ->once()
            ->with($method, m::type(Message::class), $responseClass)
            ->andReturnUsing(function (string $method, Message $request) use ($response): Message {
                $this->request = $request;

                return $response;
            });
    }
}
