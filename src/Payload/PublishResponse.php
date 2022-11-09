<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Payload;

/**
 * @see https://centrifugal.dev/docs/server/proxy#publish-proxy
 */
class PublishResponse implements ResponseInterface
{
    /**
     * @param array $data An optional JSON data to send into a channel instead of original data sent by a client.
     * @param bool $skipHistory When set to true Centrifugo won't save publication to the channel history.
     */
    public function __construct(
        public readonly array $data = [],
        public readonly bool $skipHistory = false,
    ) {
    }
}
