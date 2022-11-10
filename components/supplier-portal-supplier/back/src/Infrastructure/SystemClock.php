<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure;

use Akeneo\SupplierPortal\Supplier\Domain\Clock;

final class SystemClock implements Clock
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', new \DateTimeZone(self::TIMEZONE));
    }
}
