<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Application\SearchJobExecution;

interface ClockInterface
{
    public function now(): \DateTimeInterface;
}
