<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Request;

use RoadRunner\Centrifugo\Payload\ResponseInterface;

/**
 * @psalm-type HeadersList = array<string, array<array-key, string>>
 */
interface RequestInterface
{
    /**
     * Get data from the request.
     */
    public function getData(): array;

    /**
     * Get additional attributes from the request.
     */
    public function getAttributes(): array;

    /**
     * Get attribute from the request by name.
     */
    public function getAttribute(string $name, mixed $default = null): mixed;

    /**
     * Add attribute to the request.
     */
    public function withAttribute(string $name, mixed $value): self;

    /**
     * Send response to Centrifugo server.
     */
    public function respond(ResponseInterface $response): void;

    /**
     * Send error response to Centrifugo server.
     */
    public function error(int $code, string $message, bool $temporary = false): void;

    /**
     * Send disconnect response to Centrifugo server.
     *
     * @param bool $reconnect This parameter is no longer used since v2.0.1 due to the removal of this option in
     * centrifugal/centrifugo v5.0.0 API. It will be removed in v3.0.0.
     */
    public function disconnect(int $code, string $reason, bool $reconnect = false): void;
}
