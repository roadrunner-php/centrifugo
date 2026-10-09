<?php

declare(strict_types=1);

namespace RoadRunner\Centrifugo\Tests\Unit;

use Spiral\RoadRunner\WorkerInterface;

abstract class TestCase
{
    protected function createWorker(callable $respondCallback): WorkerInterface
    {
        $worker = \Mockery::mock(WorkerInterface::class)->shouldIgnoreMissing();
        $worker->shouldReceive('respond')->once()->andReturnUsing($respondCallback);

        return $worker;
    }
}
