<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use RoadRunner\Centrifugo\CentrifugoApiInterface;
use RoadRunner\Centrifugo\Exception\CentrifugoApiResponseException;
use RoadRunner\Centrifugo\Payload\Disconnect;
use RoadRunner\Centrifugo\RPCCentrifugoApi;
use RoadRunner\Centrifugal\API\DTO\V1 as DTO;
use Spiral\Goridge\RPC\Codec\ProtobufCodec;
use Spiral\Goridge\RPC\CodecInterface;
use Spiral\Goridge\RPC\RPCInterface;

final class RPCCentrifugoApiTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private m\MockInterface|RPCInterface $rpc;
    private CentrifugoApiInterface $api;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rpc = m::mock(RPCInterface::class);
        $this->rpc->shouldReceive('withCodec')->once()->withArgs(
            static fn(CodecInterface $codec): bool => $codec instanceof ProtobufCodec
        )->andReturnSelf();

        $this->api = new RPCCentrifugoApi($this->rpc);
    }

    public function testPublish(): void
    {
        $this->rpc->shouldReceive('call')
            ->once()
            ->withArgs(fn(
                string $method,
                DTO\PublishRequest $request,
                string $responseClass
            ): bool => $method === 'centrifuge.Publish'
                && $request->getChannel() === 'foo-channel'
                && $request->getData() === \json_encode(['foo' => 'bar'])
                && $request->getSkipHistory() === true
                && self::tagsOf($request) === ['baz', 'baf']
                && $responseClass === DTO\PublishResponse::class
            )
            ->andReturn(new DTO\PublishResponse);

        $this->api->publish(channel: 'foo-channel', message: \json_encode(['foo' => 'bar']), skipHistory: true, tags: ['baz', 'baf']);
    }

    public function testPublishErrorHandling(): void
    {
        $this->expectException(CentrifugoApiResponseException::class);
        $this->expectExceptionMessage('Error message');
        $this->expectExceptionCode(500);

        $this->rpc->shouldReceive('call')
            ->once()
            ->andReturn(
                new DTO\PublishResponse([
                    'error' => new DTO\Error([
                        'code' => 500,
                        'message' => 'Error message',
                    ]),
                ])
            );

        $this->api->publish(channel: 'foo-channel', message: \json_encode(['foo' => 'bar']), skipHistory: true, tags: ['baz', 'baf']);
    }

    public function testDisconnectWithDisconnectObject(): void
    {
        $this->rpc->shouldReceive('call')
            ->once()
            ->withArgs(fn(
                string $method,
                DTO\DisconnectRequest $request,
                string $responseClass
            ): bool => $method === 'centrifuge.Unsubscribe'
                && $request->getUser() === 'foo-user'
                && $request->getClient() === 'foo-client'
                && $request->getSession() === 'foo-session'
                && $request->getDisconnect()->getCode() === 400
                && $request->getDisconnect()->getReason() === 'foo-reason'
                && $responseClass === DTO\DisconnectResponse::class
            )
            ->andReturn(new DTO\DisconnectResponse);

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
            ->withArgs(fn(
                string $method,
                DTO\DisconnectRequest $request,
                string $responseClass
            ): bool => $method === 'centrifuge.Unsubscribe'
                && $request->getUser() === 'foo-user'
                && $request->getClient() === 'foo-client'
                && $request->getSession() === 'foo-session'
                && $request->getDisconnect()->getCode() === 400
                && $request->getDisconnect()->getReason() === 'foo-reason'
                && $responseClass === DTO\DisconnectResponse::class
            )
            ->andReturn(new DTO\DisconnectResponse);

        $this->api->disconnect(
            user: 'foo-user',
            client: 'foo-client',
            session: 'foo-session',
            disconnect: new Disconnect(code: 400, reason: 'foo-reason', reconnect: true),
        );
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
}
