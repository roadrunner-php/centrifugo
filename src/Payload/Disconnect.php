<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Payload;

final class Disconnect
{
    public function __construct(
        public readonly int $code,
        public readonly string $reason,
        public readonly bool $reconnect = false
    ) {
    }
}