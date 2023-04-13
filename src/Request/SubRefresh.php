<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Request;

use RoadRunner\Centrifugal\Proxy\DTO\V1 as DTO;
use RoadRunner\Centrifugo\Payload\SubRefreshResponse;
use RoadRunner\Centrifugo\Payload\ResponseInterface;
use Spiral\RoadRunner\WorkerInterface;

/**
 * @see https://centrifugal.dev/docs/server/proxy#sub-refresh-proxy
 */
class SubRefresh extends AbstractRequest
{
    public function __construct(
        WorkerInterface $worker,
        public readonly string $client,
        public readonly string $transport,
        public readonly string $protocol,
        public readonly string $encoding,
        public readonly string $user,
        public readonly string $channel,
        public readonly array $meta,
        public readonly array $headers
    ) {
        parent::__construct($worker);
    }

    /**
     * @param SubRefreshResponse $response
     * @psalm-suppress MoreSpecificImplementedParamType
     * @throws \JsonException
     */
    public function respond(ResponseInterface $response): void
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        \assert($response instanceof SubRefreshResponse);

        $result = $this->mapResponse($response);
        $responseObject = $this->getResponseObject();
        $responseObject->setResult($result);

        $this->sendResponse($responseObject);
    }

    protected function getResponseObject(): DTO\SubRefreshResponse
    {
        return new DTO\SubRefreshResponse();
    }

    /**
     * @throws \JsonException
     */
    private function mapResponse(SubRefreshResponse $response): DTO\SubRefreshResult
    {
        $result = new DTO\SubRefreshResult();
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
