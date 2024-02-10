<a href="https://roadrunner.dev" target="_blank">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://github.com/roadrunner-server/.github/assets/8040338/e6bde856-4ec6-4a52-bd5b-bfe78736c1ff">
    <img align="center" src="https://github.com/roadrunner-server/.github/assets/8040338/040fb694-1dd3-4865-9d29-8e0748c2c8b8">
  </picture>
</a>

# RoadRunner Centrifugo Bridge

[![PHP Version Require](https://poser.pugx.org/roadrunner-php/centrifugo/require/php)](https://packagist.org/packages/roadrunner-php/centrifugo)
[![Latest Stable Version](https://poser.pugx.org/roadrunner-php/centrifugo/v/stable)](https://packagist.org/packages/roadrunner-php/centrifugo)
[![phpunit](https://github.com/roadrunner-php/centrifugo/actions/workflows/phpunit.yml/badge.svg)](https://github.com/roadrunner-php/centrifugo/actions)
[![psalm](https://github.com/roadrunner-php/centrifugo/actions/workflows/psalm.yml/badge.svg)](https://github.com/roadrunner-php/centrifugo/actions)
[![Codecov](https://codecov.io/gh/roadrunner-php/centrifugo/branch/master/graph/badge.svg)](https://codecov.io/gh/roadrunner-php/centrifugo/)
[![Total Downloads](https://poser.pugx.org/roadrunner-php/centrifugo/downloads)](https://packagist.org/packages/roadrunner-php/centrifugo)

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

## Proxy Centrifugo requests to a PHP application

It's possible to proxy some client connection events from Centrifugo to the RoadRunner application server and react to
them in a custom way. For example, it's possible to authenticate connection via request from Centrifugo to application
backend, refresh client sessions and answer to RPC calls sent by a client over bidirectional connection.

The list of events that can be proxied:

* `connect` – called when a client connects to Centrifugo, so it's possible to authenticate user, return custom data to a
client, subscribe connection to several channels, attach meta information to the connection, and so on. Works for
bidirectional and unidirectional transports.
* `refresh` - called when a client session is going to expire, so it's possible to prolong it or just let it expire. Can
also be used just as a periodical connection liveness callback from Centrifugo to app backend. Works for bidirectional
and unidirectional transports.
* `sub_refresh` - called when it's time to refresh the subscription. Centrifugo itself will ask your backend about subscription validity instead of subscription refresh workflow on the client-side.
* `subscribe` - called when clients try to subscribe on a channel, so it's possible to check permissions and return custom
initial subscription data. Works for bidirectional transports only.
* `publish` - called when a client tries to publish into a channel, so it's possible to check permissions and optionally
modify publication data. Works for bidirectional transports only.
* `rpc` - called when a client sends RPC, you can do whatever logic you need based on a client-provided RPC method and
params. Works for bidirectional transports only.

First you need to add `centrifuge` section to your RoadRunner configuration. For example, such a configuration
would be quite feasible to run:

```yaml
rpc:
  listen: tcp://127.0.0.1:6001

server:
  command: "php app.php"
  relay: pipes

centrifuge:
  proxy_address: "tcp://0.0.0.0:10001" # Centrifugo address
```

and centrifugo config:

```json
{
  "admin": true,
  "api_key": "secret",
  "admin_password": "password",
  "admin_secret": "admin_secret",
  "allowed_origins": [
    "*"
  ],
  "token_hmac_secret_key": "test",
  "publish": true,
  "proxy_publish": true,
  "proxy_subscribe": true,
  "proxy_connect": true,
  "allow_subscribe_for_client": true,
  "proxy_connect_endpoint": "grpc://127.0.0.1:10001",
  "proxy_connect_timeout": "10s",
  "proxy_publish_endpoint": "grpc://127.0.0.1:10001",
  "proxy_publish_timeout": "10s",
  "proxy_subscribe_endpoint": "grpc://127.0.0.1:10001",
  "proxy_subscribe_timeout": "10s",
  "proxy_refresh_endpoint": "grpc://127.0.0.1:10001",
  "proxy_refresh_timeout": "10s",
  "proxy_sub_refresh_endpoint": "grpc://127.0.0.1:10001",
  "proxy_sub_refresh_timeout": "1s",
  "proxy_rpc_endpoint": "grpc://127.0.0.1:10001",
  "proxy_rpc_timeout": "10s"
}
```

> **Note**
> `proxy_connect_endpoint`, `proxy_publish_endpoint`, `proxy_subscribe_endpoint`, `proxy_refresh_endpoint`,
> `proxy_sub_refresh_endpoint`, `proxy_rpc_endpoint` - endpoint address of roadrunner server with activated
> `centrifuge` plugin.

To init abstract RoadRunner worker:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use RoadRunner\Centrifugo\CentrifugoWorker;
use RoadRunner\Centrifugo\Payload;
use RoadRunner\Centrifugo\Request;
use RoadRunner\Centrifugo\Request\RequestFactory;
use Spiral\RoadRunner\Worker;

$worker = Worker::create();
$requestFactory = new RequestFactory($worker);

// Create a new Centrifugo Worker from global environment
$centrifugoWorker = new CentrifugoWorker($worker, $requestFactory);

while ($request = $centrifugoWorker->waitRequest()) {

    if ($request instanceof Request\Invalid) {
        $errorMessage = $request->getException()->getMessage();

        if ($request->getException() instanceof \RoadRunner\Centrifugo\Exception\InvalidRequestTypeException) {
            $payload = $request->getException()->payload;
        }

        // Handle invalid request
        // $logger->error($errorMessage, $payload ?? []);

        continue;
    }

    if ($request instanceof Request\Refresh) {
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

    if ($request instanceof Request\Subscribe) {
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

    if ($request instanceof Request\Publish) {
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

    if ($request instanceof Request\RPC) {
        try {
            $response = $router->handle(
                new Request(uri: $request->method, data: $request->data),
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

> **Note**
> You can find addition information about [here](https://centrifugal.dev/docs/server/proxy).

<a href="https://spiral.dev/">
<img src="https://user-images.githubusercontent.com/773481/220979012-e67b74b5-3db1-41b7-bdb0-8a042587dedc.jpg" alt="try Spiral Framework" />
</a>

## License

The MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information. Maintained
by [Spiral Scout](https://spiralscout.com).


