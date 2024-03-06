<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Request;

use RoadRunner\Centrifugal\Proxy\DTO\V1\Disconnect;
use RoadRunner\Centrifugal\Proxy\DTO\V1\Error;
use Spiral\RoadRunner\Payload;
use Spiral\RoadRunner\WorkerInterface;

/**
 * @psalm-import-type ResponseDTO from RequestFactory
 */
abstract class AbstractRequest implements RequestInterface
{
    private array $attributes = [];

    public function __construct(
        private readonly WorkerInterface $worker,
        private readonly array $data = [],
    ) {
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getData(): array
    {
        return $this->data;
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
            new Error(compact('code', 'message', 'temporary')),
        );

        $this->sendResponse($response);
    }

    /**
     * @param bool $reconnect This parameter is no longer used since v2.0.1 due to the removal of this option in
     * centrifugal/centrifugo v5.0.0 API. It will be removed in v3.0.0.
     */
    final public function disconnect(int $code, string $reason, bool $reconnect = false): void
    {
        $response = $this->getResponseObject();
        $response->setDisconnect(
            new Disconnect(\compact('code', 'reason')),
        );

        $this->sendResponse($response);
    }

    /**
     * @param ResponseDTO $response
     * @psalm-suppress InternalClass
     * @psalm-suppress InternalMethod
     */
    protected function sendResponse(object $response): void
    {
        $this->worker->respond(
            new Payload($response->serializeToString()),
        );
    }
}
