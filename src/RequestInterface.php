<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo;

/**
 * @psalm-type HeadersList = array<string, array<array-key, string>>
 */
interface RequestInterface
{
    public function respond(object $response): void;
    public function error(int $code, string $message, bool $temporary = false): void;
    public function disconnect(int $code, string $reason, bool $reconnect = false): void;

}