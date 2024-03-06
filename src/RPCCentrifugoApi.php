<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo;

use Google\Protobuf\Internal\Message;
use RoadRunner\Centrifugo\Exception\CentrifugoApiResponseException;
use RoadRunner\Centrifugo\Payload\Disconnect;
use Spiral\Goridge\RPC\Codec\ProtobufCodec;
use Spiral\Goridge\RPC\RPCInterface;
use RoadRunner\Centrifugal\API\DTO\V1 as DTO;

/**
 * @psalm-type ResponseDTO = DTO\PublishResponse|DTO\BroadcastResponse|DTO\RefreshResponse|DTO\SubscribeResponse|DTO\UnsubscribeResponse|DTO\DisconnectResponse|DTO\PresenceResponse|DTO\PresenceStatsResponse|DTO\ChannelsResponse|DTO\BlockUserResponse|DTO\UnblockUserResponse
 * @psalm-type RequestDTO = DTO\PublishRequest|DTO\BroadcastRequest|DTO\RefreshRequest|DTO\SubscribeRequest|DTO\UnsubscribeRequest|DTO\DisconnectRequest|DTO\PresenceRequest|DTO\PresenceStatsRequest|DTO\ChannelsRequest|DTO\BlockUserRequest|DTO\UnblockUserRequest
 */
final class RPCCentrifugoApi implements CentrifugoApiInterface
{
    private readonly RPCInterface $rpc;

    public function __construct(RPCInterface $rpc)
    {
        $this->rpc = $rpc->withCodec(new ProtobufCodec());
    }

    public function publish(string $channel, string $message, bool $skipHistory = true, array $tags = [],): void
    {
        $request = new DTO\PublishRequest();
        $request->setChannel($channel);
        $request->setSkipHistory($skipHistory);
        $request->setData($message);

        if ($tags !== []) {
            $request->setTags($tags);
        }

        $this->call('centrifuge.Publish', $request, DTO\PublishResponse::class);
    }

    public function broadcast(array $channels, string $message, bool $skipHistory = true, array $tags = [],): void
    {
        $request = new DTO\BroadcastRequest();
        $request->setChannels($channels);
        $request->setSkipHistory($skipHistory);
        $request->setData($message);

        if ($tags !== []) {
            $request->setTags($tags);
        }

        $this->call('centrifuge.Broadcast', $request, DTO\BroadcastResponse::class);
    }

    public function refresh(
        string $user,
        ?string $client = null,
        ?string $session = null,
        bool $expired = false,
        ?\DateTimeInterface $expireAt = null,
    ): void {
        $request = new DTO\RefreshRequest();
        $request->setUser($user);
        $request->setExpired($expired);

        if ($client !== null) {
            $request->setClient($client);
        }

        if ($session !== null) {
            $request->setSession($session);
        }

        if ($expireAt !== null) {
            $request->setExpireAt($expireAt->getTimestamp());
        }

        $this->call('centrifuge.Refresh', $request, DTO\RefreshResponse::class);
    }

    public function subscribe(
        string $channel,
        string $user,
        ?\DateTimeInterface $expireAt = null,
        array $info = [],
        ?string $client = null,
        array $data = [],
        ?string $session = null,
    ): void {
        $request = new DTO\SubscribeRequest();
        $request->setChannel($channel);
        $request->setUser($user);

        if ($expireAt !== null) {
            $request->setExpireAt($expireAt->getTimestamp());
        }

        if ($data !== []) {
            $request->setData(\json_encode($data, JSON_THROW_ON_ERROR));
        }

        if ($info !== []) {
            $request->setInfo(\json_encode($info, JSON_THROW_ON_ERROR));
        }

        if ($client !== null) {
            $request->setClient($client);
        }

        if ($session !== null) {
            $request->setSession($session);
        }

        $this->call('centrifuge.Subscribe', $request, DTO\SubscribeResponse::class);
    }

    public function unsubscribe(string $channel, string $user, ?string $client = null, ?string $session = null,): void
    {
        $request = new DTO\UnsubscribeRequest();
        $request->setChannel($channel);
        $request->setUser($user);

        if ($client !== null) {
            $request->setClient($client);
        }

        if ($session !== null) {
            $request->setSession($session);
        }

        $this->call('centrifuge.Unsubscribe', $request, DTO\UnsubscribeResponse::class);
    }

    public function disconnect(
        string $user,
        ?string $client = null,
        array $whitelist = [],
        ?string $session = null,
        ?Disconnect $disconnect = null,
    ): void {
        $request = new DTO\DisconnectRequest();
        $request->setUser($user);

        if ($client !== null) {
            $request->setClient($client);
        }

        if ($session !== null) {
            $request->setSession($session);
        }

        if ($whitelist !== []) {
            $request->setWhitelist($whitelist);
        }

        if ($disconnect !== null) {
            $request->setDisconnect(
                new DTO\Disconnect([
                    'code' => $disconnect->code,
                    'reason' => $disconnect->reason,
                ])
            );
        }

        $this->call('centrifuge.Unsubscribe', $request, DTO\DisconnectResponse::class);
    }

    public function presence(string $channel): array
    {
        $request = new DTO\PresenceRequest();
        $request->setChannel($channel);

        $data = [];
        $response = $this->call(
            'centrifuge.Presence',
            $request,
            DTO\PresenceResponse::class
        );

        /** @var array<non-empty-string, DTO\ClientInfo> $result */
        $result = $response->getResult()?->getPresence() ?? [];

        foreach ($result as $clientId => $info) {
            $data[$clientId] = [
                'client' => $info->getClient(),
                'user' => $info->getUser(),
                'conn_info' => $info->getConnInfo(),
                'chan_info' => $info->getChanInfo(),
            ];
        }

        return $data;
    }

    public function presenceStats(string $channel): array
    {
        $request = new DTO\PresenceStatsRequest();
        $request->setChannel($channel);

        $response = $this->call(
            'centrifuge.PresenceStats',
            $request,
            DTO\PresenceStatsResponse::class
        );

        return [
            'num_clients' => $response->getResult()?->getNumClients() ?? 0,
            'num_users' => $response->getResult()?->getNumUsers() ?? 0,
        ];
    }

    public function channels(?string $pattern = null): array
    {
        $request = new DTO\ChannelsRequest();
        if ($pattern !== null) {
            $request->setPattern($pattern);
        }

        $response = $this->call(
            'centrifuge.Channels',
            $request,
            DTO\ChannelsResponse::class
        );

        $data = [];

        /** @var array<non-empty-string, DTO\ChannelInfo> $result */
        $result = $response->getResult()?->getChannels() ?? [];

        foreach ($result as $channel => $info) {
            $data[$channel] = [
                'num_clients' => $info->getNumClients(),
            ];
        }

        return $data;
    }

    public function blockUser(string $user, ?\DateTimeInterface $expireAt = null): void
    {
        $request = new DTO\BlockUserRequest();
        $request->setUser($user);
        if ($expireAt !== null) {
            $request->setExpireAt($expireAt->getTimestamp());
        }

        $this->call('centrifuge.BlockUser', $request, DTO\BlockUserResponse::class);
    }

    public function unblockUser(string $user): void
    {
        $request = new DTO\UnblockUserRequest();
        $request->setUser($user);

        $this->call('centrifuge.UnblockUser', $request, DTO\UnblockUserResponse::class);
    }

    /**
     * Make an RPC call to the Centrifugo server.
     *
     * @template T of object
     * @param non-empty-string $method
     * @param RequestDTO $request
     * @param class-string<T> $responseClass
     * @return T
     * @throws CentrifugoApiResponseException
     *
     * @psalm-suppress MixedInferredReturnType
     * @psalm-suppress MismatchingDocblockReturnType
     * @psalm-suppress RedundantConditionGivenDocblockType
     */
    private function call(string $method, Message $request, string $responseClass): Message
    {
        /** @var ResponseDTO $response */
        $response = $this->rpc->call($method, $request, $responseClass);
        \assert($response instanceof $responseClass);

        $error = $response->getError();

        if ($error !== null) {
            throw CentrifugoApiResponseException::createFromError($error);
        }

        return $response;
    }
}
