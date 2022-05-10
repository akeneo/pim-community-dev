<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Clock;

class SystemClock implements ClockInterface
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}
