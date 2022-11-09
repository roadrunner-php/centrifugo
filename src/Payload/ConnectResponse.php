<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Payload;

/**
 * @see https://centrifugal.dev/docs/server/proxy#connect-proxy
 */
class ConnectResponse implements ResponseInterface
{
    /**
     * @param string $user User ID (calculated on app backend based on request cookie header for example).
     *                     Return it as an empty string for accepting unauthenticated requests.
     * @param int|\DateTimeInterface|null $expireAt A timestamp when connection must be considered expired. If not set or set to 0
     *                           connection won't expire at all.
     * @param array $data A custom data to send to the client in connect command response.
     * @param array $info A connection info JSON.
     * @param non-empty-string[] $channels Allows providing a list of server-side channels to subscribe connection to.
     *                        @see https://centrifugal.dev/docs/server/server_subs
     * @param array $meta A custom data to attach to connection (this won't be exposed to client-side).
     * @param array<non-empty-string, SubscribeOption> $subscriptions
     */
    public function __construct(
        public readonly string $user = '',
        public readonly int|\DateTimeInterface|null $expireAt = null,
        public readonly array $data = [],
        public readonly array $info = [],
        public readonly array $channels = [],
        public readonly array $meta = [],
        public readonly array $subscriptions = [],
    ) {
    }
}
