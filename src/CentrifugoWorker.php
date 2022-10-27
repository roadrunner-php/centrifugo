<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo;

use Spiral\RoadRunner\WorkerInterface;

final class CentrifugoWorker implements CentrifugoWorkerInterface
{
    public function __construct(
        private readonly WorkerInterface $worker,
        private readonly RequestFactory $requestFactory
    ) {
    }

    public function waitRequest(): RequestInterface|null
    {
        $payload = $this->worker->waitPayload();
        if ($payload === null || (!$payload->body && !$payload->header)) {
            return null;
        }

        return $this->requestFactory->createFromPayload($payload);
    }

    public function getWorker(): WorkerInterface
    {
        return $this->worker;
    }
}