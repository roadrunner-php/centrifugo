<?php

namespace RoadRunner\Centrifugo\Request;

use Throwable;

final class InvalidRequestWrapper
{
    public function __construct(
        private readonly ?Throwable $throwable
    ) {
    }

    public function getException(): ?Throwable
    {
        return $this->throwable;
    }
}
