<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Request;

use RoadRunner\Centrifugo\DTO;
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
        public readonly array $data,
        public readonly array $headers
    ) {
        parent::__construct($worker);
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param RPCResponse $response
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function respond(object $response): void
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        \assert($response instanceof RPCResponse);

        $result = $this->mapResponse($response);

        $response = $this->getResponseObject();
        $response->setResult($result);

        $this->sendResponse($response);
    }

    private function mapResponse(RPCResponse $response): DTO\RPCResult
    {
        return new DTO\RPCResult([
            'data' => \json_encode($response->data),
        ]);
    }

    protected function getResponseObject(): DTO\RPCResponse
    {
        return new DTO\RPCResponse();
    }
}