<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit\Request;

use Testo\Test;
use Testo\Assert;
use Testo\Expect;
use RoadRunner\Centrifugo\Request\Invalid;
use RoadRunner\Centrifugo\Tests\Unit\TestCase;
use RoadRunner\Centrifugo\Payload\ResponseInterface;

#[Test]
final class InvalidTest extends TestCase
{
    public function testGetExceptionReturnsThrowable(): void
    {
        $exception = new \Exception('Test Exception');
        $request = new Invalid($exception);

        Assert::same($request->getException(), $exception);
    }

    public function testGetResponseObjectThrowsRuntimeException(): void
    {
        Expect::exception(\RuntimeException::class)->withMessageContaining('Invalid request cannot be responded');

        $request = new Invalid(new \Exception('Test Exception'));

        $reflection = new \ReflectionClass(Invalid::class);
        $method = $reflection->getMethod('getResponseObject');
        $method->setAccessible(true);
        $method->invoke($request);
    }

    public function testRespondThrowsRuntimeException(): void
    {
        Expect::exception(\RuntimeException::class)->withMessageContaining('Invalid request cannot be responded');

        $request = new Invalid(new \Exception('Test Exception'));
        $response = \Mockery::mock(ResponseInterface::class)->shouldIgnoreMissing();

        $request->respond($response);
    }

    public function testSendResponseThrowsRuntimeException(): void
    {
        Expect::exception(\RuntimeException::class)->withMessageContaining('Invalid request cannot be responded');

        $request = new Invalid(new \Exception('Test Exception'));
        $response = new \stdClass();

        $reflection = new \ReflectionClass(Invalid::class);
        $method = $reflection->getMethod('sendResponse');
        $method->setAccessible(true);
        $method->invoke($request, $response);
    }
}
