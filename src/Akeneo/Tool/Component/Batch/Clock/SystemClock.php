<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Clock;

class SystemClock implements ClockInterface
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
