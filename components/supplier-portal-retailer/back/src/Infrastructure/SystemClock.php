<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure;

use Akeneo\SupplierPortal\Retailer\Domain\Clock;

final class SystemClock implements Clock
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', new \DateTimeZone(self::TIMEZONE));
    }
}
