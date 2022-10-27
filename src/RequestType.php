<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo;

enum RequestType: string
{
    case Connect = 'connect';
    case Refresh = 'refresh';
    case Publish = 'publish';
    case Subscribe = 'subscribe';
    case RPC = 'rpc';
}