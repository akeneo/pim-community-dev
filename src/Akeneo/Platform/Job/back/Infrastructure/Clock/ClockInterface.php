<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Infrastructure\Clock;

interface ClockInterface
{
    public function now(): \DateTimeImmutable;
}
