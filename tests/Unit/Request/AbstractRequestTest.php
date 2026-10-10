<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Request;

use Mockery\MockInterface;
use RoadRunner\Centrifugal\Proxy\DTO\V1\ConnectResponse;
use RoadRunner\Centrifugal\Proxy\DTO\V1\Disconnect;
use RoadRunner\Centrifugal\Proxy\DTO\V1\Error;
use RoadRunner\Centrifugo\Request\AbstractRequest;
use RoadRunner\Centrifugo\Tests\Unit\TestCase;
use Spiral\RoadRunner\Payload;
use Spiral\RoadRunner\WorkerInterface;
use Testo\Assert;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

#[Test]
final class AbstractRequestTest extends TestCase
{
    private AbstractRequest $req;

    public function testGetData(): void
    {
        Assert::same($this->req->getData(), ['foo' => 'bar']);
    }

    public function testGetAttributes(): void
    {
        $req = $this->createRequest(\Mockery::mock(WorkerInterface::class)->shouldIgnoreMissing());

        Assert::same($req->withAttribute('foo', 'bar')->getAttributes(), ['foo' => 'bar']);
    }

    public function testGetAttribute(): void
    {
        Assert::null($this->req->getAttribute('foo'));
        Assert::same($this->req->getAttribute('foo', 'bar'), 'bar');
        Assert::same($this->req->withAttribute('foo', 'baz')->getAttribute('foo'), 'baz');
        Assert::same($this->req->withAttribute('foo', 'baz')->getAttribute('foo', 'bar'), 'baz');
    }

    public function testWithAttribute(): void
    {
        $newReq = $this->req->withAttribute('foo', 'bar');

        Assert::notEquals($this->req, $newReq);
        Assert::same($newReq->getAttributes(), ['foo' => 'bar']);
        Assert::same($this->req->getAttributes(), []);
    }

    public function testTemporaryError(): void
    {
        $worker = $this->createWorker(function (Payload $arg) {
            $expects = new Payload(
                (new ConnectResponse())
                    ->setError(new Error(['code' => 500, 'message' => 'some error', 'temporary' => true]))
                    ->serializeToString(),
            );

            Assert::equals($arg, $expects);
        });

        $req = $this->createRequest($worker);
        $req->shouldReceive('getResponseObject')->once()->andReturn(new ConnectResponse());

        $req->error(500, 'some error', true);
    }

    public function testError(): void
    {
        $worker = $this->createWorker(function (Payload $arg) {
            $expects = new Payload(
                (new ConnectResponse())
                    ->setError(new Error(['code' => 500, 'message' => 'some error', 'temporary' => false]))
                    ->serializeToString(),
            );

            Assert::equals($arg, $expects);
        });

        $req = $this->createRequest($worker);
        $req->shouldReceive('getResponseObject')->once()->andReturn(new ConnectResponse());

        $req->error(500, 'some error');
    }

    public function testDisconnect(): void
    {
        $worker = $this->createWorker(function (Payload $arg) {
            $expects = new Payload(
                (new ConnectResponse())
                    ->setDisconnect(new Disconnect(['code' => 111, 'reason' => 'some']))
                    ->serializeToString(),
            );

            Assert::equals($arg, $expects);
        });

        $req = $this->createRequest($worker);
        $req->shouldReceive('getResponseObject')->once()->andReturn(new ConnectResponse());

        $req->disconnect(111, 'some');
    }

    public function testDisconnectWithDeprecatedReconnect(): void
    {
        $worker = $this->createWorker(function (Payload $arg) {
            $expects = new Payload(
                (new ConnectResponse())
                    ->setDisconnect(new Disconnect(['code' => 111, 'reason' => 'some']))
                    ->serializeToString(),
            );

            Assert::equals($arg, $expects);
        });

        $req = $this->createRequest($worker);
        $req->shouldReceive('getResponseObject')->once()->andReturn(new ConnectResponse());

        $req->disconnect(111, 'some', true);
    }

    #[BeforeTest]
    protected function setUp(): void
    {
        $this->req = $this->createRequest(
            \Mockery::mock(WorkerInterface::class)->shouldIgnoreMissing(),
            ['foo' => 'bar'],
        );
    }

    private function createRequest(WorkerInterface $worker, array $data = []): AbstractRequest&MockInterface
    {
        return \Mockery::mock(AbstractRequest::class, [$worker, $data])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }
}
