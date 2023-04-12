<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo;

use RoadRunner\Centrifugo\Payload\Disconnect;

/**
 * @see https://centrifugal.dev/docs/server/server_api
 */
interface CentrifugoApiInterface
{
    /**
     * Publish command allows publishing data into a channel (we call this message publication in Centrifugo).
     * Most probably this is a command you'll use most of the time.
     *
     * @param non-empty-string $channel
     * @param string $message JSON encoded string
     * @param array<non-empty-string, non-empty-string> $tags
     */
    public function publish(
        string $channel,
        string $message,
        bool $skipHistory = true,
        array $tags = [],
    ): void;

    /**
     * Similar to publish but allows to send the same data into many channels.
     *
     * @param non-empty-string[] $channels
     * @param string $message JSON encoded string
     * @param array<non-empty-string, non-empty-string> $tags
     */
    public function broadcast(
        array $channels,
        string $message,
        bool $skipHistory = true,
        array $tags = [],
    ): void;

    /**
     * Allows refreshing user connection (mostly useful when unidirectional transports are used).
     *
     * @param non-empty-string|null $client
     * @param non-empty-string|null $session
     */
    public function refresh(
        string $user,
        ?string $client = null,
        ?string $session = null,
        bool $expired = false,
        ?\DateTimeInterface $expireAt = null,
    ): void;

    /**
     * Allows subscribing user to a channel.
     *
     * @param non-empty-string $channel
     * @param non-empty-string|null $client
     * @param non-empty-string|null $session
     */
    public function subscribe(
        string $channel,
        string $user,
        ?\DateTimeInterface $expireAt = null,
        array $info = [],
        ?string $client = null,
        array $data = [],
        ?string $session = null,
    ): void;

    /**
     * Allows unsubscribing user from a channel.
     *
     * @param non-empty-string $channel
     * @param non-empty-string|null $client
     * @param non-empty-string|null $session
     */
    public function unsubscribe(
        string $channel,
        string $user,
        ?string $client = null,
        ?string $session = null,
    ): void;

    /**
     * Allows disconnecting a user by ID.
     *
     * @param non-empty-string|null $client
     * @param non-empty-string[] $whitelist
     * @param non-empty-string|null $session
     */
    public function disconnect(
        string $user,
        ?string $client = null,
        array $whitelist = [],
        ?string $session = null,
        ?Disconnect $disconnect = null,
    ): void;

    /**
     * Allows getting channel online presence information (all clients currently subscribed on this channel).
     *
     * @param non-empty-string $channel
     * @return array<non-empty-string, array{
     *     client: string,
     *     user: string,
     *     conn_info: string,
     *     chan_info: string
     * }>
     */
    public function presence(
        string $channel
    ): array;

    /**
     * Allows getting short channel presence information - number of clients and number of unique users
     * (based on user ID).
     *
     * @param non-empty-string $channel
     * @return array{
     *     num_clients: int,
     *     num_users: int
     * }
     */
    public function presenceStats(
        string $channel
    ): array;

    /**
     * Return active channels (with one or more active subscribers in it).
     *
     * @param non-empty-string|null $pattern
     * @return array<non-empty-string, array{num_clients: int}>
     */
    public function channels(
        ?string $pattern = null
    ): array;

    /**
     * Block user.
     *
     * @param non-empty-string $user
     */
    public function blockUser(string $user, ?\DateTimeInterface $expireAt = null): void;

    /**
     * Unblock user.
     *
     * @param non-empty-string $user
     */
    public function unblockUser(string $user): void;
}