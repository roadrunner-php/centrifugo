<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Request;

use RoadRunner\Centrifugo\Payload\ResponseInterface;

final class Invalid extends AbstractRequest
{
    private const RESPONSE_EXCEPTION_MESSAGE = 'Invalid request cannot be responded';

    public function __construct(
        private readonly \Throwable $exception
    ) {
    }

    public function getException(): \Throwable
    {
        return $this->exception;
    }

    protected function getResponseObject(): object
    {
        throw new \RuntimeException(self::RESPONSE_EXCEPTION_MESSAGE);
    }

    public function respond(ResponseInterface $response): void
    {
        throw new \RuntimeException(self::RESPONSE_EXCEPTION_MESSAGE);
    }

    protected function sendResponse(object $response): void
    {
        throw new \RuntimeException(self::RESPONSE_EXCEPTION_MESSAGE);
    }
}
