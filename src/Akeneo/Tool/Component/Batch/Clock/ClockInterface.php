<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Clock;

interface ClockInterface
{
    public function now(): \DateTimeImmutable;
}
