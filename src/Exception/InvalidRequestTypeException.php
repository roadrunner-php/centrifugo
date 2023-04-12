<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Exception;

use Spiral\RoadRunner\Payload as WorkerPayload;

class InvalidRequestTypeException extends \RuntimeException
{
    public function __construct(
        public readonly WorkerPayload $payload,
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
