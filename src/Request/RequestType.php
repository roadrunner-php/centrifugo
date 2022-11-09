<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Request;

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
            $request instanceof Connect => self::Connect,
            $request instanceof Subscribe => self::Subscribe,
            $request instanceof Refresh => self::Refresh,
            $request instanceof Publish => self::Publish,
            $request instanceof RPC => self::RPC,
            default => throw new InvalidRequestTypeException(
                \sprintf('Request type `%s` is not supported', $request::class)
            ),
        };
    }
}
