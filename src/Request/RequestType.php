<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Request;

use RoadRunner\Centrifugo\Exception\InvalidRequestTypeException;

enum RequestType: string
{
    case Connect = 'connect';
    case Refresh = 'refresh';
    case SubRefresh = 'sub_refresh';
    case Publish = 'publish';
    case Subscribe = 'subscribe';
    case RPC = 'rpc';
    case Invalid = 'invalid';

    public static function createFrom(RequestInterface $request): self
    {
        return match (true) {
            $request instanceof Connect => RequestType::Connect,
            $request instanceof Subscribe => RequestType::Subscribe,
            $request instanceof Refresh => RequestType::Refresh,
            $request instanceof SubRefresh => RequestType::SubRefresh,
            $request instanceof Publish => RequestType::Publish,
            $request instanceof RPC => RequestType::RPC,
            $request instanceof Invalid => RequestType::Invalid,
        };
    }
}
