<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Request;

use RoadRunner\Centrifugal\Proxy\DTO\V1 as DTO;
use RoadRunner\Centrifugo\Payload\PublishResponse;
use RoadRunner\Centrifugo\Payload\ResponseInterface;
use Spiral\RoadRunner\WorkerInterface;

/**
 * @see https://centrifugal.dev/docs/server/proxy#publish-proxy
 */
final class Publish extends AbstractRequest
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
        array $data,
        public readonly array $headers
    ) {
        parent::__construct($worker, $data);
    }

    /**
     * @param PublishResponse $response
     * @psalm-suppress MoreSpecificImplementedParamType
     * @throws \JsonException
     */
    public function respond(ResponseInterface $response): void
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        \assert($response instanceof PublishResponse);

        $result = $this->mapResponse($response);
        $responseObject = $this->getResponseObject();
        $responseObject->setResult($result);

        $this->sendResponse($responseObject);
    }

    protected function getResponseObject(): DTO\PublishResponse
    {
        return new DTO\PublishResponse();
    }

    /**
     * @throws \JsonException
     */
    private function mapResponse(PublishResponse $response): DTO\PublishResult
    {
        $result = new DTO\PublishResult();

        $result->setSkipHistory($response->skipHistory);

        if ($response->data !== []) {
            $result->setData(\json_encode($response->data, JSON_THROW_ON_ERROR));
        }

        return $result;
    }
}
