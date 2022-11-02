<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Exception;

use RoadRunner\Centrifugo\Service\DTO\Error;

class CentrifugApiResponseException extends \Exception
{
    public static function createFromError(Error $error): self
    {
        return new self($error->getMessage(), $error->getCode());
    }
}