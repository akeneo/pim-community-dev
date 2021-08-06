<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Clock;

class SystemClock implements ClockInterface
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}
