<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Payload;

final class Disconnect
{
    /**
     * @deprecated
     */
    public bool $reconnect = false;

    /**
     * @param bool $reconnect This parameter is no longer used since v2.0.1 due to the removal of this option in
     * centrifugal/centrifugo v5.0.0 API. It will be removed in v3.0.0.
     */
    public function __construct(
        public readonly int $code,
        public readonly string $reason,
        bool $reconnect = false
    ) {
        /** @psalm-suppress DeprecatedProperty */
        $this->reconnect = $reconnect;
    }
}
