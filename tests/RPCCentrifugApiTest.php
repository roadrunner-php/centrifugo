<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use RoadRunner\Centrifugo\CentrifugApiInterface;
use RoadRunner\Centrifugo\Exception\CentrifugApiResponseException;
use RoadRunner\Centrifugo\RPCCentrifugApi;
use RoadRunner\Centrifugo\Service\DTO;
use Spiral\Goridge\RPC\Codec\ProtobufCodec;
use Spiral\Goridge\RPC\CodecInterface;
use Spiral\Goridge\RPC\RPCInterface;

final class RPCCentrifugApiTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private m\MockInterface|RPCInterface $rpc;
    private CentrifugApiInterface $api;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rpc = m::mock(RPCInterface::class);
        $this->rpc->shouldReceive('withCodec')->once()->withArgs(
            static fn(CodecInterface $codec): bool => $codec instanceof ProtobufCodec
        )->andReturnSelf();

        $this->api = new RPCCentrifugApi($this->rpc);
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
                && \iterator_to_array($request->getTags()->getIterator()) === ['baz', 'baf']
                && $responseClass === DTO\PublishResponse::class
            )
            ->andReturn(new DTO\PublishResponse);

        $this->api->publish(channel: 'foo-channel', message: \json_encode(['foo' => 'bar']), skipHistory: true, tags: ['baz', 'baf']);
    }

    public function testPublishErrorHandling(): void
    {
        $this->expectException(CentrifugApiResponseException::class);
        $this->expectErrorMessage('Error message');
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
}