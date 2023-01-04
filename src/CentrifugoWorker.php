<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo;

use RoadRunner\Centrifugo\Request\InvalidRequestWrapper;
use RoadRunner\Centrifugo\Request\RequestFactory;
use RoadRunner\Centrifugo\Request\RequestInterface;
use Spiral\RoadRunner\WorkerInterface;

final class CentrifugoWorker implements CentrifugoWorkerInterface
{
    public function __construct(
        private readonly WorkerInterface $worker,
        private readonly RequestFactory $requestFactory
    ) {
    }

    /**
     * @throws \JsonException
     */
    public function waitRequest(): null|RequestInterface|InvalidRequestWrapper
    {
        try {
            $payload = $this->worker->waitPayload();
            if ($payload === null || (!$payload->body && !$payload->header)) {
                return null;
            }

            return $this->requestFactory->createFromPayload($payload);
        } catch (\Throwable $throwable) {
            return new InvalidRequestWrapper($throwable);
        }
    }

    public function getWorker(): WorkerInterface
    {
        return $this->worker;
    }
}
