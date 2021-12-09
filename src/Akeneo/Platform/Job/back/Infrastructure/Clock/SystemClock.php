<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Clock;

use Akeneo\Platform\Job\Application\SearchJobExecution\ClockInterface;

class SystemClock implements ClockInterface
{
    public function now(): \DateTimeInterface
    {
        return new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
