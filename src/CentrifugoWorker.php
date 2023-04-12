<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo;

use RoadRunner\Centrifugo\Request\RequestFactory;
use RoadRunner\Centrifugo\Request\RequestInterface;
use Spiral\RoadRunner\WorkerInterface;

final class CentrifugoWorker implements CentrifugoWorkerInterface
{
    public function __construct(
        private readonly WorkerInterface $worker,
        private readonly RequestFactory $requestFactory,
    ) {
    }

    /**
     * @throws \JsonException
     * @psalm-suppress InternalProperty
     */
    public function waitRequest(): ?RequestInterface
    {
        try {
            $payload = $this->worker->waitPayload();
            if ($payload === null || (!$payload->body && !$payload->header)) {
                return null;
            }

            return $this->requestFactory->createFromPayload($payload);
        } catch (\Throwable $e) {
            return $this->requestFactory->createFromException($e);
        }
    }

    public function getWorker(): WorkerInterface
    {
        return $this->worker;
    }
}
