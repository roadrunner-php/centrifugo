<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo;

use RoadRunner\Centrifugo\DTO\Disconnect;
use RoadRunner\Centrifugo\DTO\Error;
use Spiral\RoadRunner\WorkerInterface;

/**
 * @psalm-import-type ResponseDTO from RequestFactory
 */
abstract class AbstractRequest implements RequestInterface
{
    private array $attributes;

    public function __construct(
        private readonly WorkerInterface $worker,
    ) {
    }


    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    public function withAttribute(string $name, mixed $value): self
    {
        $self = clone $this;
        $self->attributes[$name] = $value;

        return $self;
    }

    /**
     * @return ResponseDTO
     */
    abstract protected function getResponseObject(): object;

    final public function error(int $code, string $message, bool $temporary = false): void
    {
        $response = $this->getResponseObject();
        $response->setError(
            new Error(compact('code', 'message', 'temporary'))
        );

        $this->sendResponse($response);
    }

    final public function disconnect(int $code, string $reason, bool $reconnect = false): void
    {
        $response = $this->getResponseObject();
        $response->setDisconnect(
            new Disconnect(compact('code', 'reason', 'reconnect'))
        );

        $this->sendResponse($response);
    }

    /**
     * @param ResponseDTO $response
     */
    final protected function sendResponse(object $response): void
    {
        $this->worker->respond(
            new \Spiral\RoadRunner\Payload(
                $response->serializeToString()
            )
        );
    }
}