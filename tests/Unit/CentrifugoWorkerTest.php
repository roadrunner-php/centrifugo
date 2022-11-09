<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RoadRunner\Centrifugo\CentrifugoWorker;
use RoadRunner\Centrifugo\Request\Connect;
use RoadRunner\Centrifugo\Request\Publish;
use RoadRunner\Centrifugo\Request\Refresh;
use RoadRunner\Centrifugo\Request\RequestFactory;
use RoadRunner\Centrifugo\Request\RequestType;
use RoadRunner\Centrifugo\Request\RPC;
use RoadRunner\Centrifugo\Request\Subscribe;
use RoadRunner\Centrifugo\DTO;
use Spiral\RoadRunner\Payload;
use Spiral\RoadRunner\WorkerInterface;

final class CentrifugoWorkerTest extends TestCase
{
    public function testConnectRequest(): void
    {
        $worker = \Mockery::mock(WorkerInterface::class);

        $worker->shouldReceive('waitPayload')->once()
            ->andReturn(
                $this->createPayload(
                    new DTO\ConnectRequest([
                        'client' => 'client-id',
                        'transport' => 'webscoket',
                        'protocol' => 'http',
                        'encoding' => 'utf8',
                        'data' => \json_encode(['foo' => 'bar']),
                        'name' => 'request-name',
                        'version' => '1.0.0',
                        'channels' => ['public', 'private'],
                    ])
                )
            );

        $centrifugo = new CentrifugoWorker($worker, new RequestFactory($worker));

        $request = $centrifugo->waitRequest();

        $this->assertInstanceOf(Connect::class, $request);

        $this->assertSame('client-id', $request->client);
        $this->assertSame('webscoket', $request->transport);
        $this->assertSame('http', $request->protocol);
        $this->assertSame('utf8', $request->encoding);
        $this->assertSame(['foo' => 'bar'], $request->getData());
        $this->assertSame('request-name', $request->name);
        $this->assertSame('1.0.0', $request->version);
        $this->assertSame(['public', 'private'], $request->channels);
        $this->assertSame(['type' => ['connect']], $request->headers);
    }

    public function testRefreshRequest(): void
    {
        $worker = \Mockery::mock(WorkerInterface::class);

        $worker->shouldReceive('waitPayload')->once()
            ->andReturn(
                $this->createPayload(
                    new DTO\RefreshRequest([
                        'client' => 'client-id',
                        'transport' => 'webscoket',
                        'protocol' => 'http',
                        'encoding' => 'utf8',
                        'user' => 'user-1',
                        'meta' => \json_encode(['foo' => 'bar']),
                    ])
                )
            );

        $centrifugo = new CentrifugoWorker($worker, new RequestFactory($worker));

        $request = $centrifugo->waitRequest();

        $this->assertInstanceOf(Refresh::class, $request);

        $this->assertSame('client-id', $request->client);
        $this->assertSame('webscoket', $request->transport);
        $this->assertSame('http', $request->protocol);
        $this->assertSame('utf8', $request->encoding);
        $this->assertSame('user-1', $request->user);
        $this->assertSame(['foo' => 'bar'], $request->meta);
        $this->assertSame(['type' => ['refresh']], $request->headers);
    }

    public function testSubscribeRequest(): void
    {
        $worker = \Mockery::mock(WorkerInterface::class);

        $worker->shouldReceive('waitPayload')->once()
            ->andReturn(
                $this->createPayload(
                    new DTO\SubscribeRequest([
                        'client' => 'client-id',
                        'transport' => 'webscoket',
                        'protocol' => 'http',
                        'encoding' => 'utf8',
                        'user' => 'user-1',
                        'channel' => 'public',
                        'token' => 'foo-token',
                        'meta' => \json_encode(['foo' => 'bar']),
                        'data' => \json_encode(['baz' => 'bar']),
                    ])
                )
            );

        $centrifugo = new CentrifugoWorker($worker, new RequestFactory($worker));

        $request = $centrifugo->waitRequest();

        $this->assertInstanceOf(Subscribe::class, $request);

        $this->assertSame('client-id', $request->client);
        $this->assertSame('webscoket', $request->transport);
        $this->assertSame('http', $request->protocol);
        $this->assertSame('utf8', $request->encoding);
        $this->assertSame('user-1', $request->user);
        $this->assertSame('public', $request->channel);
        $this->assertSame('foo-token', $request->token);
        $this->assertSame(['foo' => 'bar'], $request->meta);
        $this->assertSame(['baz' => 'bar'], $request->getData());
        $this->assertSame(['type' => ['subscribe']], $request->headers);
    }

    public function testPublishRequest(): void
    {
        $worker = \Mockery::mock(WorkerInterface::class);

        $worker->shouldReceive('waitPayload')->once()
            ->andReturn(
                $this->createPayload(
                    new DTO\PublishRequest([
                        'client' => 'client-id',
                        'transport' => 'webscoket',
                        'protocol' => 'http',
                        'encoding' => 'utf8',
                        'user' => 'user-1',
                        'channel' => 'private',
                        'meta' => \json_encode(['foo' => 'bar']),
                        'data' => \json_encode(['baz' => 'bar']),
                    ])
                )
            );

        $centrifugo = new CentrifugoWorker($worker, new RequestFactory($worker));

        $request = $centrifugo->waitRequest();

        $this->assertInstanceOf(Publish::class, $request);

        $this->assertSame('client-id', $request->client);
        $this->assertSame('webscoket', $request->transport);
        $this->assertSame('http', $request->protocol);
        $this->assertSame('utf8', $request->encoding);
        $this->assertSame('user-1', $request->user);
        $this->assertSame('private', $request->channel);
        $this->assertSame(['foo' => 'bar'], $request->meta);
        $this->assertSame(['baz' => 'bar'], $request->getData());
        $this->assertSame(['type' => ['publish']], $request->headers);
    }

    public function testRPCRequest(): void
    {
        $worker = \Mockery::mock(WorkerInterface::class);

        $worker->shouldReceive('waitPayload')->once()
            ->andReturn(
                $this->createPayload(
                    new DTO\RPCRequest([
                        'client' => 'client-id',
                        'transport' => 'webscoket',
                        'protocol' => 'http',
                        'encoding' => 'utf8',
                        'user' => 'user-1',
                        'method' => 'user.show',
                        'meta' => \json_encode(['foo' => 'bar']),
                        'data' => \json_encode(['baz' => 'bar']),
                    ])
                )
            );

        $centrifugo = new CentrifugoWorker($worker, new RequestFactory($worker));

        $request = $centrifugo->waitRequest();

        $this->assertInstanceOf(RPC::class, $request);

        $this->assertSame('client-id', $request->client);
        $this->assertSame('webscoket', $request->transport);
        $this->assertSame('http', $request->protocol);
        $this->assertSame('utf8', $request->encoding);
        $this->assertSame('user-1', $request->user);
        $this->assertSame('user.show', $request->method);
        $this->assertSame(['foo' => 'bar'], $request->meta);
        $this->assertSame(['baz' => 'bar'], $request->getData());
        $this->assertSame(['type' => ['rpc']], $request->headers);
    }

    private function createPayload(object $request): Payload
    {
        $type = match (true) {
            $request instanceof DTO\ConnectRequest => RequestType::Connect,
            $request instanceof DTO\PublishRequest => RequestType::Publish,
            $request instanceof DTO\SubscribeRequest => RequestType::Subscribe,
            $request instanceof DTO\RefreshRequest => RequestType::Refresh,
            $request instanceof DTO\RPCRequest => RequestType::RPC,
            default => throw new \InvalidArgumentException('Invalid request object ' . $request::class)
        };

        return new Payload(
            $request->serializeToString(),
            \json_encode(['type' => [$type->value]])
        );
    }
}