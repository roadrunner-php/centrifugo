<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Spiral\RoadRunner\WorkerInterface;

abstract class TestCase extends PHPUnitTestCase
{
    protected function createWorker(callable $respondCallback): WorkerInterface
    {
        $worker = $this->createMock(WorkerInterface::class);
        $worker
            ->expects($this->once())
            ->method('respond')
            ->willReturnCallback($respondCallback);

        return $worker;
    }
}
