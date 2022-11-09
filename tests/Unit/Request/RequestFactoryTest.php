<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Request;

use RoadRunner\Centrifugo\DTO\ConnectRequest;
use RoadRunner\Centrifugo\DTO\PublishRequest;
use RoadRunner\Centrifugo\DTO\RefreshRequest;
use RoadRunner\Centrifugo\DTO\RPCRequest;
use RoadRunner\Centrifugo\DTO\SubscribeRequest;
use RoadRunner\Centrifugo\Request\Connect;
use RoadRunner\Centrifugo\Request\Publish;
use RoadRunner\Centrifugo\Request\Refresh;
use RoadRunner\Centrifugo\Request\RequestFactory;
use RoadRunner\Centrifugo\Request\RPC;
use RoadRunner\Centrifugo\Request\Subscribe;
use RoadRunner\Centrifugo\Tests\Unit\TestCase;
use Spiral\RoadRunner\Payload;
use Spiral\RoadRunner\WorkerInterface;

final class RequestFactoryTest extends TestCase
{
    private RequestFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new RequestFactory($this->createMock(WorkerInterface::class));
    }

    public function testCreateConnectRequest(): void
    {
        /** @var Connect $request */
        $request = $this->factory->createFromPayload(new Payload(
            (new ConnectRequest([
                'data' => json_encode(['some']),
                'client' => 'a',
                'transport' => 'b',
                'protocol' => 'f',
                'encoding' => 'h',
                'version' => '1',
                'channels' => ['some'],
            ]))->serializeToString(),
            json_encode(['type' => ['connect']])
        ));

        $this->assertInstanceOf(Connect::class, $request);
        $this->assertSame(['some'], $request->getData());
        $this->assertSame('a', $request->client);
        $this->assertSame('b', $request->transport);
        $this->assertSame('f', $request->protocol);
        $this->assertSame('h', $request->encoding);
        $this->assertSame('1', $request->version);
        $this->assertSame(['some'], $request->channels);
        $this->assertSame(['type' => ['connect']], $request->headers);
    }

    public function testCreateRefreshRequest(): void
    {
        /** @var Refresh $request */
        $request = $this->factory->createFromPayload(new Payload(
            (new RefreshRequest([
                'client' => 'a',
                'transport' => 'b',
                'protocol' => 'f',
                'encoding' => 'h',
                'user' => 'n',
                'meta' => json_encode(['some']),
            ]))->serializeToString(),
            json_encode(['type' => ['refresh']])
        ));

        $this->assertInstanceOf(Refresh::class, $request);
        $this->assertSame('a', $request->client);
        $this->assertSame('b', $request->transport);
        $this->assertSame('f', $request->protocol);
        $this->assertSame('h', $request->encoding);
        $this->assertSame('n', $request->user);
        $this->assertSame(['some'], $request->meta);
        $this->assertSame(['type' => ['refresh']], $request->headers);
    }

    public function testCreateSubscribeRequest(): void
    {
        /** @var Subscribe $request */
        $request = $this->factory->createFromPayload(new Payload(
            (new SubscribeRequest([
                'client' => 'a',
                'transport' => 'b',
                'protocol' => 'f',
                'encoding' => 'h',
                'user' => 'n',
                'channel' => 'j',
                'token' => 'd',
                'meta' => json_encode(['some']),
                'data' => json_encode(['other']),
            ]))->serializeToString(),
            json_encode(['type' => ['subscribe']])
        ));

        $this->assertInstanceOf(Subscribe::class, $request);
        $this->assertSame('a', $request->client);
        $this->assertSame('b', $request->transport);
        $this->assertSame('f', $request->protocol);
        $this->assertSame('h', $request->encoding);
        $this->assertSame('n', $request->user);
        $this->assertSame('j', $request->channel);
        $this->assertSame('d', $request->token);
        $this->assertSame(['some'], $request->meta);
        $this->assertSame(['other'], $request->getData());
        $this->assertSame(['type' => ['subscribe']], $request->headers);
    }

    public function testCreatePublishRequest(): void
    {
        /** @var Publish $request */
        $request = $this->factory->createFromPayload(new Payload(
            (new PublishRequest([
                'client' => 'a',
                'transport' => 'b',
                'protocol' => 'f',
                'encoding' => 'h',
                'user' => 'n',
                'channel' => 'j',
                'meta' => json_encode(['some']),
                'data' => json_encode(['other']),
            ]))->serializeToString(),
            json_encode(['type' => ['publish']])
        ));

        $this->assertInstanceOf(Publish::class, $request);
        $this->assertSame('a', $request->client);
        $this->assertSame('b', $request->transport);
        $this->assertSame('f', $request->protocol);
        $this->assertSame('h', $request->encoding);
        $this->assertSame('n', $request->user);
        $this->assertSame('j', $request->channel);
        $this->assertSame(['some'], $request->meta);
        $this->assertSame(['other'], $request->getData());
        $this->assertSame(['type' => ['publish']], $request->headers);
    }

    public function testCreateRPCRequest(): void
    {
        /** @var RPC $request */
        $request = $this->factory->createFromPayload(new Payload(
            (new RPCRequest([
                'client' => 'a',
                'transport' => 'b',
                'protocol' => 'f',
                'encoding' => 'h',
                'user' => 'n',
                'method' => 'g',
                'meta' => json_encode(['some']),
                'data' => json_encode(['other']),
            ]))->serializeToString(),
            json_encode(['type' => ['rpc']])
        ));

        $this->assertInstanceOf(RPC::class, $request);
        $this->assertSame('a', $request->client);
        $this->assertSame('b', $request->transport);
        $this->assertSame('f', $request->protocol);
        $this->assertSame('h', $request->encoding);
        $this->assertSame('n', $request->user);
        $this->assertSame('g', $request->method);
        $this->assertSame(['some'], $request->meta);
        $this->assertSame(['other'], $request->getData());
        $this->assertSame(['type' => ['rpc']], $request->headers);
    }
}
