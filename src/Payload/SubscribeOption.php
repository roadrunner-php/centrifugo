<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Payload;

class SubscribeOption
{
    public function __construct(
        public readonly int|\DateTimeInterface|null $expireAt = null,
        public readonly array $info = [],
        public readonly array $data = [],
        public readonly Override|null $override = null,
    ) {
    }
}
