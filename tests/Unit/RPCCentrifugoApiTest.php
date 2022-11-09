<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use RoadRunner\Centrifugo\CentrifugoApiInterface;
use RoadRunner\Centrifugo\Exception\CentrifugoApiResponseException;
use RoadRunner\Centrifugo\RPCCentrifugoApi;
use RoadRunner\Centrifugo\Service\DTO;
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
                && \iterator_to_array($request->getTags()->getIterator()) === ['baz', 'baf']
                && $responseClass === DTO\PublishResponse::class
            )
            ->andReturn(new DTO\PublishResponse);

        $this->api->publish(channel: 'foo-channel', message: \json_encode(['foo' => 'bar']), skipHistory: true, tags: ['baz', 'baf']);
    }

    public function testPublishErrorHandling(): void
    {
        $this->expectException(CentrifugoApiResponseException::class);
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