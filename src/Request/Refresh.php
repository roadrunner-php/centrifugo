<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Request;

use RoadRunner\Centrifugal\Proxy\DTO\V1 as DTO;
use RoadRunner\Centrifugo\Payload\RefreshResponse;
use RoadRunner\Centrifugo\Payload\ResponseInterface;
use Spiral\RoadRunner\WorkerInterface;

/**
 * @see https://centrifugal.dev/docs/server/proxy#refresh-proxy
 */
class Refresh extends AbstractRequest
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

    /**
     * @param RefreshResponse $response
     * @psalm-suppress MoreSpecificImplementedParamType
     * @throws \JsonException
     */
    public function respond(ResponseInterface $response): void
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        \assert($response instanceof RefreshResponse);

        $result = $this->mapResponse($response);
        $responseObject = $this->getResponseObject();
        $responseObject->setResult($result);

        $this->sendResponse($responseObject);
    }

    protected function getResponseObject(): DTO\RefreshResponse
    {
        return new DTO\RefreshResponse();
    }

    /**
     * @throws \JsonException
     */
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
            $result->setInfo(\json_encode($response->info, JSON_THROW_ON_ERROR));
        }

        return $result;
    }
}
