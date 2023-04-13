<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Request;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RoadRunner\Centrifugo\Request\Connect;
use RoadRunner\Centrifugo\Request\Invalid;
use RoadRunner\Centrifugo\Request\Publish;
use RoadRunner\Centrifugo\Request\Refresh;
use RoadRunner\Centrifugo\Request\RequestInterface;
use RoadRunner\Centrifugo\Request\RequestType;
use RoadRunner\Centrifugo\Request\RPC;
use RoadRunner\Centrifugo\Request\SubRefresh;
use RoadRunner\Centrifugo\Request\Subscribe;

final class RequestTypeTest extends TestCase
{
    #[DataProvider('requestTypeDataProvider')]
    public function testRequestType(RequestInterface $request, RequestType $expected): void
    {
        $this->assertSame($expected, RequestType::createFrom($request));
    }

    public static function requestTypeDataProvider(): \Traversable
    {
        $worker = \Mockery::mock(\Spiral\RoadRunner\WorkerInterface::class);

        yield 'Connect' => [
            new Connect($worker, '', '', '', '', [], '', '', [], []),
            RequestType::Connect,
        ];

        yield 'Subscribe' => [
            new Subscribe($worker, '', '', '', '', '',  '', '', [], [], []),
            RequestType::Subscribe,
        ];

        yield 'Refresh' => [
            new Refresh($worker, '', '', '', '', '',  [], []),
            RequestType::Refresh,
        ];

        yield 'SubRefresh' => [
            new SubRefresh($worker, '', '', '', '', '',  '', [], []),
            RequestType::SubRefresh,
        ];

        yield 'Publish' => [
            new Publish($worker, '', '', '', '', '', '', [], [], []),
            RequestType::Publish,
        ];

        yield 'RPC' => [
            new RPC($worker, '', '', '', '', '',  '', [], [], []),
            RequestType::RPC,
        ];

        yield 'Invalid' => [
            new Invalid(new \Exception('Test Exception')),
            RequestType::Invalid,
        ];
    }
}
