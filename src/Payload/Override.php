<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Payload;

/**
 * @see https://centrifugal.dev/docs/server/proxy#subscribe-proxy
 */
class Override
{
    /**
     * @param bool|null $presence Override presence.
     * @param bool|null $joinLeave Override join_leave.
     * @param bool|null $forcePushJoinLeave Override force_push_join_leave.
     * @param bool|null $forcePositioning Override force_positioning
     * @param bool|null $forceRecovery Override force_recovery
     */
    public function __construct(
        public readonly ?bool $presence = null,
        public readonly ?bool $joinLeave = null,
        public readonly ?bool $forcePushJoinLeave = null,
        public readonly ?bool $forcePositioning = null,
        public readonly ?bool $forceRecovery = null,
    ) {
    }
}
