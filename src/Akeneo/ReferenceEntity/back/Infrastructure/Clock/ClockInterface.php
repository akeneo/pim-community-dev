<?php

namespace Akeneo\ReferenceEntity\Infrastructure\Clock;

interface ClockInterface
{
    public function now(): \DateTimeImmutable;
}
