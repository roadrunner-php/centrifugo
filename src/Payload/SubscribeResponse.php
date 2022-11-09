<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Payload;

/**
 * @see https://centrifugal.dev/docs/server/proxy#subscribe-proxy
 */
class SubscribeResponse implements ResponseInterface
{
    /**
     * @param array $info A channel info JSON.
     * @param array $data A custom data to send to the client in subscribe command reply.
     * @param non-empty-string[] $allow
     * @param Override|null $override Allows dynamically override some channel options defined in Centrifugo configuration
     *                                on a per-connection basis (see below available fields)
     */
    public function __construct(
        public readonly array $info = [],
        public readonly array $data = [],
        public readonly array $allow = [],
        public readonly Override|null $override = null,
    ) {
    }
}
