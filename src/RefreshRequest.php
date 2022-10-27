<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo;

use RoadRunner\Centrifugo\DTO;
use RoadRunner\Centrifugo\Payload\RefreshResponse;
use Spiral\RoadRunner\WorkerInterface;

/**
 * @see https://centrifugal.dev/docs/server/proxy#refresh-proxy
 */
class RefreshRequest extends AbstractRequest
{
    public function __construct(
        WorkerInterface $worker,
        public readonly string $client,
        public readonly string $transport,
        public readonly string $protocol,
        public readonly string $encoding,
        public readonly string $user,
        public readonly array $meta,
        public readonly array $headers
    ) {
        parent::__construct($worker);
    }

    public function respond(object $response): void
    {
        \assert($response instanceof RefreshResponse);

        $result = $this->mapResponse($response);
        $response = $this->getResponseObject();
        $response->setResult($result);

        $this->sendResponse($response);
    }

    protected function getResponseObject(): DTO\RefreshResponse
    {
        return new DTO\RefreshResponse();
    }

    private function mapResponse(RefreshResponse $response): DTO\RefreshResult
    {
        $result = new DTO\RefreshResult();
        $result->setExpired($response->expired);

        if ($response->expireAt !== null) {
            $expireAt = $response->expireAt instanceof \DateTimeInterface
                ? $response->expireAt->getTimestamp()
                : $response->expireAt;

            $result->setExpireAt($expireAt);
        }

        if ($response->info !== []) {
            $result->setInfo(\json_encode($response->info));
        }

        return $result;
    }
}