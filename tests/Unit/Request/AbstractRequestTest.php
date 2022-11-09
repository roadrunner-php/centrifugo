<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Request;

use RoadRunner\Centrifugo\DTO\ConnectResponse;
use RoadRunner\Centrifugo\DTO\Disconnect;
use RoadRunner\Centrifugo\DTO\Error;
use RoadRunner\Centrifugo\Request\AbstractRequest;
use RoadRunner\Centrifugo\Tests\Unit\TestCase;
use Spiral\RoadRunner\Payload;
use Spiral\RoadRunner\WorkerInterface;

final class AbstractRequestTest extends TestCase
{
    private AbstractRequest $req;

    protected function setUp(): void
    {
        $this->req = $this->getMockForAbstractClass(AbstractRequest::class, [
            $this->createMock(WorkerInterface::class),
            ['foo' => 'bar']
        ]);
    }

    public function testGetData(): void
    {
        $this->assertSame(['foo' => 'bar'], $this->req->getData());
    }

    /**
     * @dataProvider attributesDataProvider
     */
    public function testGetAttributes(AbstractRequest $request, array $expected): void
    {
        $this->assertSame($expected, $request->getAttributes());
    }

    public function testGetAttribute(): void
    {
        $this->assertNull($this->req->getAttribute('foo'));
        $this->assertSame('bar', $this->req->getAttribute('foo', 'bar'));
        $this->assertSame('baz', $this->req->withAttribute('foo', 'baz')->getAttribute('foo'));
        $this->assertSame('baz', $this->req->withAttribute('foo', 'baz')->getAttribute('foo', 'bar'));
    }

    public function testWithAttribute(): void
    {
        $newReq = $this->req->withAttribute('foo', 'bar');

        $this->assertNotEquals($newReq, $this->req);
        $this->assertSame(['foo' => 'bar'], $newReq->getAttributes());
        $this->assertSame([], $this->req->getAttributes());
    }

    public function attributesDataProvider(): \Traversable
    {
        $req = $this->getMockForAbstractClass(AbstractRequest::class, [
            $this->createMock(WorkerInterface::class)
        ]);

        yield [$req, []];
        yield [$req->withAttribute('foo', 'bar'), ['foo' => 'bar']];
    }

    public function testTemporaryError(): void
    {
        $worker = $this->createWorker(function (Payload $arg) {
            $expects = new Payload((new ConnectResponse())
                ->setError(new Error(['code' => 500, 'message' => 'some error', 'temporary' => true]))
                ->serializeToString()
            );

            $this->assertEquals($expects, $arg);
        });

        $req = $this->getMockForAbstractClass(AbstractRequest::class, [$worker]);
        $req
            ->expects($this->once())
            ->method('getResponseObject')
            ->willReturn(new ConnectResponse());

        $req->error(500, 'some error', true);
    }

    public function testError(): void
    {
        $worker = $this->createWorker(function (Payload $arg) {
            $expects = new Payload((new ConnectResponse())
                ->setError(new Error(['code' => 500, 'message' => 'some error', 'temporary' => false]))
                ->serializeToString()
            );

            $this->assertEquals($expects, $arg);
        });

        $req = $this->getMockForAbstractClass(AbstractRequest::class, [$worker]);
        $req
            ->expects($this->once())
            ->method('getResponseObject')
            ->willReturn(new ConnectResponse());

        $req->error(500, 'some error');
    }

    public function testDisconnect(): void
    {
        $worker = $this->createWorker(function (Payload $arg) {
            $expects = new Payload((new ConnectResponse())
                ->setDisconnect(new Disconnect(['code' => 111, 'reason' => 'some', 'reconnect' => false]))
                ->serializeToString()
            );

            $this->assertEquals($expects, $arg);
        });

        $req = $this->getMockForAbstractClass(AbstractRequest::class, [$worker]);
        $req
            ->expects($this->once())
            ->method('getResponseObject')
            ->willReturn(new ConnectResponse());

        $req->disconnect(111, 'some');
    }

    public function testDisconnectWithReconnect(): void
    {
        $worker = $this->createWorker(function (Payload $arg) {
            $expects = new Payload((new ConnectResponse())
                ->setDisconnect(new Disconnect(['code' => 111, 'reason' => 'some', 'reconnect' => true]))
                ->serializeToString()
            );

            $this->assertEquals($expects, $arg);
        });

        $req = $this->getMockForAbstractClass(AbstractRequest::class, [$worker]);
        $req
            ->expects($this->once())
            ->method('getResponseObject')
            ->willReturn(new ConnectResponse());

        $req->disconnect(111, 'some', true);
    }
}
