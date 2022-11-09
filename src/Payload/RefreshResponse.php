<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Payload;

/**
 * @see https://centrifugal.dev/docs/server/proxy#refresh-proxy
 */
class RefreshResponse implements ResponseInterface
{
    /**
     * @param bool $expired A flag to mark the connection as expired - the client will be disconnected.
     * @param int|\DateTimeInterface|null $expireAt A timestamp in the future when connection must be considered expired.
     * @param array $info A connection info JSON.
     */
    public function __construct(
        public readonly bool $expired = false,
        public readonly int|\DateTimeInterface|null $expireAt = null,
        public readonly array $info = [],
    ) {
    }
}
