<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Fakes;

use Akeneo\SupplierPortal\Supplier\Domain\Clock;

/**
 * Clock implementation initialized with a hardcoded time to avoid calling the system clock in tests.
 */
final class FrozenClock implements Clock
{
    public const TIMEZONE = 'UTC';

    private \DateTimeImmutable $now;

    public function __construct(string $dateTime)
    {
        if ('' === $dateTime || 'now' === $dateTime) {
            throw new \InvalidArgumentException('Please provide a fixed datetime.');
        }

        $this->now = new \DateTimeImmutable($dateTime, new \DateTimeZone(self::TIMEZONE));
    }

    public function now(): \DateTimeImmutable
    {
        return $this->now;
    }
}
