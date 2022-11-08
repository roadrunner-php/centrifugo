<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo;

use RoadRunner\Centrifugo\Exception\InvalidRequestTypeException;

enum RequestType: string
{
    case Connect = 'connect';
    case Refresh = 'refresh';
    case Publish = 'publish';
    case Subscribe = 'subscribe';
    case RPC = 'rpc';

    public static function createFrom(RequestInterface $request): self
    {
        return match (true) {
            $request instanceof ConnectRequest => self::Connect,
            $request instanceof SubscribeRequest => self::Subscribe,
            $request instanceof RefreshRequest => self::Refresh,
            $request instanceof PublishRequest => self::Publish,
            $request instanceof RPCRequest => self::RPC,
            default => throw new InvalidRequestTypeException(
                \sprintf('Request type `%s` is not supported', $request::class)
            ),
        };
    }
}
