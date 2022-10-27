<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo;

use RoadRunner\Centrifugo\DTO;
use RoadRunner\Centrifugo\Payload\ConnectResponse;
use RoadRunner\Centrifugo\Payload\Override;
use RoadRunner\Centrifugo\Payload\SubscribeOption;
use Spiral\RoadRunner\WorkerInterface;

/**
 * @see https://centrifugal.dev/docs/server/proxy#connect-proxy
 */
final class ConnectRequest extends AbstractRequest
{
    /**
     * @param non-empty-string[] $channels
     */
    public function __construct(
        WorkerInterface $worker,
        public readonly string $client,
        public readonly string $transport,
        public readonly string $protocol,
        public readonly string $encoding,
        public readonly array $data,
        public readonly ?string $name,
        public readonly ?string $version,
        public readonly array $channels,
        public readonly array $headers
    ) {
        parent::__construct($worker);
    }

    public function respond(object $response): void
    {
        \assert($response instanceof ConnectResponse);

        $result = $this->mapResponse($response);
        $response = $this->getResponseObject();
        $response->setResult($result);

        $this->sendResponse($response);
    }

    private function mapResponse(ConnectResponse $response): DTO\ConnectResult
    {
        $result = new DTO\ConnectResult();
        $result->setUser($response->user);

        if ($response->expireAt !== null) {
            $result->setExpireAt($this->parseExpiresAt($response->expireAt));
        }

        if ($response->data !== []) {
            $result->setData(\json_encode($response->data));
        }

        if ($response->info !== []) {
            $result->setInfo(\json_encode($response->info));
        }

        if ($response->meta !== []) {
            $result->setMeta(\json_encode($response->meta));
        }

        if ($response->channels !== []) {
            $result->setChannels($response->channels);
        }

        if ($response->subscriptions !== []) {
            $result->setSubs($this->mapSubscriptions($response->subscriptions));
        }

        return $result;
    }

    protected function getResponseObject(): DTO\ConnectResponse
    {
        return new DTO\ConnectResponse();
    }

    /**
     * @param array<non-empty-string, SubscribeOption> $subscriptions
     * @return array<non-empty-string, DTO\SubscribeOptions>
     */
    private function mapSubscriptions(array $subscriptions): array
    {
        $subs = [];

        foreach ($subscriptions as $name => $subscription) {
            $sub = new DTO\SubscribeOptions();

            if ($subscription->expireAt) {
                $sub->setExpireAt($this->parseExpiresAt($subscription->expireAt));
            }

            if ($subscription->data !== []) {
                $sub->setData(\json_encode($subscription->data));
            }

            if ($subscription->info !== []) {
                $sub->setInfo(\json_encode($subscription->info));
            }

            if ($subscription->override !== null) {
                $sub->setOverride($this->mapSubscribeOption($subscription->override));
            }

            $subs[$name] = $sub;
        }

        return $subs;
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

    public function parseExpiresAt(\DateTimeInterface|int $expireAt): int
    {
        return $expireAt instanceof \DateTimeInterface
            ? $expireAt->getTimestamp()
            : $expireAt;
    }
}