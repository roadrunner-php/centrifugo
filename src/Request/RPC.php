<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Request;

use RoadRunner\Centrifugal\Proxy\DTO\V1 as DTO;
use RoadRunner\Centrifugo\Payload\ResponseInterface;
use RoadRunner\Centrifugo\Payload\RPCResponse;
use Spiral\RoadRunner\WorkerInterface;

/**
 * @see https://centrifugal.dev/docs/server/proxy#rpc-proxy
 */
final class RPC extends AbstractRequest
{
    public function __construct(
        WorkerInterface $worker,
        public readonly string $client,
        public readonly string $transport,
        public readonly string $protocol,
        public readonly string $encoding,
        public readonly string $user,
        public readonly ?string $method,
        public readonly array $meta,
        array $data,
        public readonly array $headers
    ) {
        parent::__construct($worker, $data);
    }

    /**
     * @param RPCResponse $response
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function respond(ResponseInterface $response): void
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        \assert($response instanceof RPCResponse);

        $result = $this->mapResponse($response);

        $responseObject = $this->getResponseObject();
        $responseObject->setResult($result);

        $this->sendResponse($responseObject);
    }

    private function mapResponse(RPCResponse $response): DTO\RPCResult
    {
        return new DTO\RPCResult([
            'data' => \json_encode($response->data, JSON_THROW_ON_ERROR),
        ]);
    }

    protected function getResponseObject(): DTO\RPCResponse
    {
        return new DTO\RPCResponse();
    }
}
