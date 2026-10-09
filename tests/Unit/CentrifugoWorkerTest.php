<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit;

use Testo\Test;
use Testo\Assert;
use RoadRunner\Centrifugal\Proxy\DTO\V1 as DTO;
use RoadRunner\Centrifugo\CentrifugoWorker;
use RoadRunner\Centrifugo\Exception\InvalidRequestTypeException;
use RoadRunner\Centrifugo\Request\Connect;
use RoadRunner\Centrifugo\Request\Invalid;
use RoadRunner\Centrifugo\Request\Publish;
use RoadRunner\Centrifugo\Request\Refresh;
use RoadRunner\Centrifugo\Request\RequestFactory;
use RoadRunner\Centrifugo\Request\RequestType;
use RoadRunner\Centrifugo\Request\RPC;
use RoadRunner\Centrifugo\Request\SubRefresh;
use RoadRunner\Centrifugo\Request\Subscribe;
use Spiral\RoadRunner\Exception\RoadRunnerException;
use Spiral\RoadRunner\Payload;
use Spiral\RoadRunner\WorkerInterface;

#[Test]
final class CentrifugoWorkerTest
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
                    ]),
                ),
            );

        $centrifugo = new CentrifugoWorker($worker, new RequestFactory($worker));

        $request = $centrifugo->waitRequest();

        Assert::instanceOf($request, Connect::class);

        Assert::same($request->client, 'client-id');
        Assert::same($request->transport, 'webscoket');
        Assert::same($request->protocol, 'http');
        Assert::same($request->encoding, 'utf8');
        Assert::same($request->getData(), ['foo' => 'bar']);
        Assert::same($request->name, 'request-name');
        Assert::same($request->version, '1.0.0');
        Assert::same($request->channels, ['public', 'private']);
        Assert::same($request->headers, ['type' => ['connect']]);
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
                    ]),
                ),
            );

        $centrifugo = new CentrifugoWorker($worker, new RequestFactory($worker));

        $request = $centrifugo->waitRequest();

        Assert::instanceOf($request, Refresh::class);

        Assert::same($request->client, 'client-id');
        Assert::same($request->transport, 'webscoket');
        Assert::same($request->protocol, 'http');
        Assert::same($request->encoding, 'utf8');
        Assert::same($request->user, 'user-1');
        Assert::same($request->meta, ['foo' => 'bar']);
        Assert::same($request->headers, ['type' => ['refresh']]);
    }

    public function testSubRefreshRequest(): void
    {
        $worker = \Mockery::mock(WorkerInterface::class);

        $worker->shouldReceive('waitPayload')->once()
            ->andReturn(
                $this->createPayload(
                    new DTO\SubRefreshRequest([
                        'client' => 'client-id',
                        'transport' => 'webscoket',
                        'protocol' => 'http',
                        'encoding' => 'utf8',
                        'user' => 'user-1',
                        'channel' => 'channel-1',
                        'meta' => \json_encode(['foo' => 'bar']),
                    ]),
                ),
            );

        $centrifugo = new CentrifugoWorker($worker, new RequestFactory($worker));

        $request = $centrifugo->waitRequest();

        Assert::instanceOf($request, SubRefresh::class);

        Assert::same($request->client, 'client-id');
        Assert::same($request->transport, 'webscoket');
        Assert::same($request->protocol, 'http');
        Assert::same($request->encoding, 'utf8');
        Assert::same($request->user, 'user-1');
        Assert::same($request->channel, 'channel-1');
        Assert::same($request->meta, ['foo' => 'bar']);
        Assert::same($request->headers, ['type' => ['sub_refresh']]);
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
                    ]),
                ),
            );

        $centrifugo = new CentrifugoWorker($worker, new RequestFactory($worker));

        $request = $centrifugo->waitRequest();

        Assert::instanceOf($request, Subscribe::class);

        Assert::same($request->client, 'client-id');
        Assert::same($request->transport, 'webscoket');
        Assert::same($request->protocol, 'http');
        Assert::same($request->encoding, 'utf8');
        Assert::same($request->user, 'user-1');
        Assert::same($request->channel, 'public');
        Assert::same($request->token, 'foo-token');
        Assert::same($request->meta, ['foo' => 'bar']);
        Assert::same($request->getData(), ['baz' => 'bar']);
        Assert::same($request->headers, ['type' => ['subscribe']]);
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
                    ]),
                ),
            );

        $centrifugo = new CentrifugoWorker($worker, new RequestFactory($worker));

        $request = $centrifugo->waitRequest();

        Assert::instanceOf($request, Publish::class);

        Assert::same($request->client, 'client-id');
        Assert::same($request->transport, 'webscoket');
        Assert::same($request->protocol, 'http');
        Assert::same($request->encoding, 'utf8');
        Assert::same($request->user, 'user-1');
        Assert::same($request->channel, 'private');
        Assert::same($request->meta, ['foo' => 'bar']);
        Assert::same($request->getData(), ['baz' => 'bar']);
        Assert::same($request->headers, ['type' => ['publish']]);
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
                    ]),
                ),
            );

        $centrifugo = new CentrifugoWorker($worker, new RequestFactory($worker));

        $request = $centrifugo->waitRequest();

        Assert::instanceOf($request, RPC::class);

        Assert::same($request->client, 'client-id');
        Assert::same($request->transport, 'webscoket');
        Assert::same($request->protocol, 'http');
        Assert::same($request->encoding, 'utf8');
        Assert::same($request->user, 'user-1');
        Assert::same($request->method, 'user.show');
        Assert::same($request->meta, ['foo' => 'bar']);
        Assert::same($request->getData(), ['baz' => 'bar']);
        Assert::same($request->headers, ['type' => ['rpc']]);
    }

    public function testInvalidPayloadRequest(): void
    {
        $worker = \Mockery::mock(WorkerInterface::class);

        $worker->shouldReceive('waitPayload')->once()
            ->andReturn('test');

        $centrifugo = new CentrifugoWorker($worker, new RequestFactory($worker));
        $request = $centrifugo->waitRequest();

        Assert::instanceOf($request, Invalid::class);
        Assert::instanceOf($request->getException(), \TypeError::class);
    }

    public function testInvalidPayloadTypeRequest(): void
    {
        $worker = \Mockery::mock(WorkerInterface::class);

        $worker->shouldReceive('waitPayload')->once()
            ->andReturn($payload = new Payload('test', \json_encode(['type' => ['test']])));

        $centrifugo = new CentrifugoWorker($worker, new RequestFactory($worker));
        $request = $centrifugo->waitRequest();

        Assert::instanceOf($request, Invalid::class);
        Assert::instanceOf($request->getException(), InvalidRequestTypeException::class);
        Assert::same($request->getException()->payload, $payload);
    }

    public function testWaitPayloadExceptionRequest(): void
    {
        $worker = \Mockery::mock(WorkerInterface::class);

        $worker->shouldReceive('waitPayload')->once()
            ->andThrow($e = new RoadRunnerException('Some error'));

        $centrifugo = new CentrifugoWorker($worker, new RequestFactory($worker));
        $request = $centrifugo->waitRequest();

        Assert::instanceOf($request, Invalid::class);
        Assert::same($request->getException(), $e);
    }

    private function createPayload(object $request): Payload
    {
        $type = match (true) {
            $request instanceof DTO\ConnectRequest => RequestType::Connect,
            $request instanceof DTO\PublishRequest => RequestType::Publish,
            $request instanceof DTO\SubscribeRequest => RequestType::Subscribe,
            $request instanceof DTO\RefreshRequest => RequestType::Refresh,
            $request instanceof DTO\SubRefreshRequest => RequestType::SubRefresh,
            $request instanceof DTO\RPCRequest => RequestType::RPC,
            default => throw new \InvalidArgumentException('Invalid request object ' . $request::class),
        };

        return new Payload(
            $request->serializeToString(),
            \json_encode(['type' => [$type->value]]),
        );
    }
}
