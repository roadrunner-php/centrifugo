<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Payload;

/**
 * @see https://centrifugal.dev/docs/server/proxy#rpc-proxy
 */
class RPCResponse implements ResponseInterface
{
    /**
     * @param array $data RPC response - any valid JSON is supported.
     */
    public function __construct(
        public readonly array $data = [],
    ) {
    }
}
