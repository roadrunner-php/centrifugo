<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Request;

use RoadRunner\Centrifugal\Proxy\DTO\V1 as DTO;
use RoadRunner\Centrifugo\Payload\Override;
use RoadRunner\Centrifugo\Payload\ResponseInterface;
use RoadRunner\Centrifugo\Payload\SubscribeResponse;
use Spiral\RoadRunner\WorkerInterface;

/**
 * @see https://centrifugal.dev/docs/server/proxy#subscribe-proxy
 */
final class Subscribe extends AbstractRequest
{
    public function __construct(
        WorkerInterface $worker,
        public readonly string $client,
        public readonly string $transport,
        public readonly string $protocol,
        public readonly string $encoding,
        public readonly string $user,
        public readonly string $channel,
        public readonly string $token,
        public readonly array $meta,
        array $data,
        public readonly array $headers
    ) {
        parent::__construct($worker, $data);
    }

    /**
     * @param SubscribeResponse $response
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function respond(ResponseInterface $response): void
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        \assert($response instanceof SubscribeResponse);

        $result = $this->mapResponse($response);
        $responseObject = $this->getResponseObject();
        $responseObject->setResult($result);

        $this->sendResponse($responseObject);
    }

    /**
     * @throws \JsonException
     */
    private function mapResponse(SubscribeResponse $response): DTO\SubscribeResult
    {
        $result = new DTO\SubscribeResult();

        if ($response->info !== []) {
            $result->setInfo(\json_encode($response->info, JSON_THROW_ON_ERROR));
        }

        if ($response->data !== []) {
            $result->setData(\json_encode($response->data, JSON_THROW_ON_ERROR));
        }

        if ($response->allow !== []) {
            $result->setAllow($response->allow);
        }

        if ($response->override !== null) {
            $result->setOverride($this->mapSubscribeOption($response->override));
        }

        return $result;
    }

    protected function getResponseObject(): DTO\SubscribeResponse
    {
        return new DTO\SubscribeResponse();
    }

    public function mapSubscribeOption(Override $override): DTO\SubscribeOptionOverride
    {
        $option = new DTO\SubscribeOptionOverride();

        if ($override->presence !== null) {
            $option->setPresence(
                new DTO\BoolValue([
                    'value' => $override->presence,
                ])
            );
        }

        if ($override->joinLeave !== null) {
            $option->setJoinLeave(
                new DTO\BoolValue([
                    'value' => $override->joinLeave,
                ])
            );
        }

        if ($override->forceRecovery !== null) {
            $option->setForceRecovery(
                new DTO\BoolValue([
                    'value' => $override->forceRecovery,
                ])
            );
        }

        if ($override->forcePositioning !== null) {
            $option->setForcePositioning(
                new DTO\BoolValue([
                    'value' => $override->forcePositioning,
                ])
            );
        }

        if ($override->forcePushJoinLeave !== null) {
            $option->setForcePushJoinLeave(
                new DTO\BoolValue([
                    'value' => $override->forcePushJoinLeave,
                ])
            );
        }
        return $option;
    }
}
