<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Request;

use RoadRunner\Centrifugo\Request\Invalid;
use RoadRunner\Centrifugo\Tests\Unit\TestCase;
use RoadRunner\Centrifugo\Payload\ResponseInterface;

final class InvalidTest extends TestCase
{
    public function testGetExceptionReturnsThrowable(): void
    {
        $exception = new \Exception('Test Exception');
        $request = new Invalid($exception);

        $this->assertSame($exception, $request->getException());
    }

    public function testGetResponseObjectThrowsRuntimeException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid request cannot be responded');

        $request = new Invalid(new \Exception('Test Exception'));

        $reflection = new \ReflectionClass(Invalid::class);
        $method = $reflection->getMethod('getResponseObject');
        $method->setAccessible(true);
        $method->invoke($request);
    }

    public function testRespondThrowsRuntimeException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid request cannot be responded');

        $request = new Invalid(new \Exception('Test Exception'));
        $response = $this->createMock(ResponseInterface::class);

        $request->respond($response);
    }

    public function testSendResponseThrowsRuntimeException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid request cannot be responded');

        $request = new Invalid(new \Exception('Test Exception'));
        $response = new \stdClass();

        $reflection = new \ReflectionClass(Invalid::class);
        $method = $reflection->getMethod('sendResponse');
        $method->setAccessible(true);
        $method->invoke($request, $response);
    }
}
