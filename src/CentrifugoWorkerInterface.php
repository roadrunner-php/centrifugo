<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo;

use Spiral\RoadRunner\WorkerAwareInterface;

interface CentrifugoWorkerInterface extends WorkerAwareInterface
{
    /**
     * Wait for incoming Websocket client request.
     */
    public function waitRequest(): RequestInterface|null;
}
