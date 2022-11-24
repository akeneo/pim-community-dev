<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure;

use Akeneo\SupplierPortal\Supplier\Infrastructure\SystemClock;
use PHPUnit\Framework\TestCase;

final class SystemClockTest extends TestCase
{
    /** @test */
    public function itInstanciatesAnImmutableDateTime(): void
    {
        $sut = new SystemClock();

        static::assertInstanceOf(\DateTimeImmutable::class, $sut->now());
    }

    /** @test */
    public function itInstanciatesADateTimeWithUTCTimezone(): void
    {
        $sut = new SystemClock();

        static::assertSame('UTC', $sut->now()->getTimezone()->getName());
    }
}
