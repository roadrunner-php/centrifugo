<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Request;

enum RequestType: string
{
    case Connect = 'connect';
    case Refresh = 'refresh';
    case Publish = 'publish';
    case Subscribe = 'subscribe';
    case RPC = 'rpc';
    case Invalid = 'invalid';

    /**
     * @deprecated Will be removed in 2.0
     */
    public static function createFrom(RequestInterface $request): self
    {
        return match (true) {
            $request instanceof Connect => self::Connect,
            $request instanceof Subscribe => self::Subscribe,
            $request instanceof Refresh => self::Refresh,
            $request instanceof Publish => self::Publish,
            $request instanceof RPC => self::RPC,
            $request instanceof Invalid => self::Invalid,
            default => throw new \InvalidArgumentException(
                \sprintf('Request type `%s` is not supported', $request::class)
            ),
        };
    }
}
