<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Request;

use Testo\Test;
use Testo\Assert;
use Testo\Lifecycle\BeforeTest;
use RoadRunner\Centrifugal\Proxy\DTO\V1\ConnectRequest;
use RoadRunner\Centrifugal\Proxy\DTO\V1\PublishRequest;
use RoadRunner\Centrifugal\Proxy\DTO\V1\RefreshRequest;
use RoadRunner\Centrifugal\Proxy\DTO\V1\RPCRequest;
use RoadRunner\Centrifugal\Proxy\DTO\V1\SubscribeRequest;
use RoadRunner\Centrifugo\Request\Connect;
use RoadRunner\Centrifugo\Request\Publish;
use RoadRunner\Centrifugo\Request\Refresh;
use RoadRunner\Centrifugo\Request\RequestFactory;
use RoadRunner\Centrifugo\Request\RPC;
use RoadRunner\Centrifugo\Request\Subscribe;
use RoadRunner\Centrifugo\Tests\Unit\TestCase;
use Spiral\RoadRunner\Payload;
use Spiral\RoadRunner\WorkerInterface;

#[Test]
final class RequestFactoryTest extends TestCase
{
    private RequestFactory $factory;

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
            json_encode(['type' => ['connect']]),
        ));

        Assert::instanceOf($request, Connect::class);
        Assert::same($request->getData(), ['some']);
        Assert::same($request->client, 'a');
        Assert::same($request->transport, 'b');
        Assert::same($request->protocol, 'f');
        Assert::same($request->encoding, 'h');
        Assert::same($request->version, '1');
        Assert::same($request->channels, ['some']);
        Assert::same($request->headers, ['type' => ['connect']]);
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
            json_encode(['type' => ['refresh']]),
        ));

        Assert::instanceOf($request, Refresh::class);
        Assert::same($request->client, 'a');
        Assert::same($request->transport, 'b');
        Assert::same($request->protocol, 'f');
        Assert::same($request->encoding, 'h');
        Assert::same($request->user, 'n');
        Assert::same($request->meta, ['some']);
        Assert::same($request->headers, ['type' => ['refresh']]);
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
            json_encode(['type' => ['subscribe']]),
        ));

        Assert::instanceOf($request, Subscribe::class);
        Assert::same($request->client, 'a');
        Assert::same($request->transport, 'b');
        Assert::same($request->protocol, 'f');
        Assert::same($request->encoding, 'h');
        Assert::same($request->user, 'n');
        Assert::same($request->channel, 'j');
        Assert::same($request->token, 'd');
        Assert::same($request->meta, ['some']);
        Assert::same($request->getData(), ['other']);
        Assert::same($request->headers, ['type' => ['subscribe']]);
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
            json_encode(['type' => ['publish']]),
        ));

        Assert::instanceOf($request, Publish::class);
        Assert::same($request->client, 'a');
        Assert::same($request->transport, 'b');
        Assert::same($request->protocol, 'f');
        Assert::same($request->encoding, 'h');
        Assert::same($request->user, 'n');
        Assert::same($request->channel, 'j');
        Assert::same($request->meta, ['some']);
        Assert::same($request->getData(), ['other']);
        Assert::same($request->headers, ['type' => ['publish']]);
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
            json_encode(['type' => ['rpc']]),
        ));

        Assert::instanceOf($request, RPC::class);
        Assert::same($request->client, 'a');
        Assert::same($request->transport, 'b');
        Assert::same($request->protocol, 'f');
        Assert::same($request->encoding, 'h');
        Assert::same($request->user, 'n');
        Assert::same($request->method, 'g');
        Assert::same($request->meta, ['some']);
        Assert::same($request->getData(), ['other']);
        Assert::same($request->headers, ['type' => ['rpc']]);
    }

    #[BeforeTest]
    protected function setUp(): void
    {
        $this->factory = new RequestFactory(\Mockery::mock(WorkerInterface::class)->shouldIgnoreMissing());
    }
}
