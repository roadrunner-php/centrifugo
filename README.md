# RoadRunner Centrifugo Bridge

[![PHP Version Require](https://poser.pugx.org/roadrunner-php/centrifugo/require/php)](https://packagist.org/packages/roadrunner-php/centrifugo)
[![Latest Stable Version](https://poser.pugx.org/roadrunner-php/centrifugo/v/stable)](https://packagist.org/packages/roadrunner-php/centrifugo)
[![phpunit](https://github.com/roadrunner-php/centrifugo/actions/workflows/phpunit.yml/badge.svg)](https://github.com/roadrunner-php/centrifugo/actions)
[![psalm](https://github.com/roadrunner-php/centrifugo/actions/workflows/psalm.yml/badge.svg)](https://github.com/roadrunner-php/centrifugo/actions)
[![Codecov](https://codecov.io/gh/roadrunner-php/centrifugo/branch/master/graph/badge.svg)](https://codecov.io/gh/roadrunner-php/centrifugo/)
[![Total Downloads](https://poser.pugx.org/roadrunner-php/centrifugo/downloads)](https://packagist.org/roadrunner-php/centrifugo)

This repository contains the codebase PHP bridge using RoadRunner centrifuge plugin.

## Installation

To install application server and Jobs codebase

```bash
composer require roadrunner-php/centrifugo
```

You can use the convenient installer to download the latest available compatible version of RoadRunner assembly:

```bash
composer require spiral/roadrunner-cli --dev
vendor/bin/rr get
```

## Usage

First you need to add `centrifuge` section to your RoadRunner configuration. For example, such a configuration
would be quite feasible to run:

```yaml
version: '2.7'

rpc:
  listen: tcp://127.0.0.1:6001

server:
  command: "php app.php"
  relay: pipes

centrifuge:
  endpoint: "ws://127.0.0.1:8000/connection/websocket"
  proxy_address: "tcp://0.0.0.0:10001"
```

To init abstract RoadRunner worker:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use RoadRunner\Centrifugo\CentrifugoWorker;
use Spiral\RoadRunner\Worker;
use RoadRunner\Centrifugo\Payload;

// Create a new Centrifugo Worker from global environment
$worker = new CentrifugoWorker(Worker::create());

while ($request = $worker->waitRequest()) {
    
    if ($request instanceof \RoadRunner\Centrifugo\ConnectRequest) {
        try {
            // Do something
            $request->respond(new Payload\ConnectResponse(
                // ...
            ));
            
            // You can also disconnect connection
            $request->disconnect('500', 'Connection is not allowed.');
        } catch (\Throwable $e) {
            $request->error($e->getCode(), $e->getMessage());
        }

        continue;
    }
    
    if ($request instanceof \RoadRunner\Centrifugo\RefreshRequest) {
        try {
            // Do something
            $request->respond(new Payload\RefreshResponse(
                // ...
            ));
        } catch (\Throwable $e) {
            $request->error($e->getCode(), $e->getMessage());
        }

        continue;
    }
    
    if ($request instanceof \RoadRunner\Centrifugo\SubscribeRequest) {
        try {
            // Do something
            $request->respond(new Payload\SubscribeResponse(
                // ...
            ));
            
            // You can also disconnect connection
            $request->disconnect('500', 'Connection is not allowed.');
        } catch (\Throwable $e) {
            $request->error($e->getCode(), $e->getMessage());
        }

        continue;
    }
    
    if ($request instanceof \RoadRunner\Centrifugo\PublishRequest) {
        try {
            // Do something
            $request->respond(new Payload\PublishResponse(
                // ...
            ));
            
            // You can also disconnect connection
            $request->disconnect('500', 'Connection is not allowed.');
        } catch (\Throwable $e) {
            $request->error($e->getCode(), $e->getMessage());
        }

        continue;
    }
    
    if ($request instanceof \RoadRunner\Centrifugo\RPCRequest) {
        try {
            $response = $router->handle(
                new Request(uri: $request->method, data: $request->data)
            ); // ['user' => ['id' => 1, 'username' => 'john_smith']]
            
            $request->respond(new Payload\RPCResponse(
                data: $response
            ));
        } catch (\Throwable $e) {
            $request->error($e->getCode(), $e->getMessage());
        }

        continue;
    }
}
```

### Proto compiling

At first, you need to download `protoc-gen-php-grpc binary` and then run compiler

```bash
vendor/bin/rr download-protoc-binary
composer compile
```

https://buf.build/roadrunner-server/api/file/main:proto/centrifugo/proxy/v1/proxy.proto

## License

The MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information. Maintained
by [Spiral Scout](https://spiralscout.com).


