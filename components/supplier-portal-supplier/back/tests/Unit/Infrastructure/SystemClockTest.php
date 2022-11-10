<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit;

use Akeneo\SupplierPortal\Supplier\Infrastructure\SystemClock;
use PHPUnit\Framework\TestCase;

final class SystemClockTest extends TestCase
{
    /** @test */
    public function itInstanciatesADateTimeImmutableObjectUsingTheUTCTimezone(): void
    {
        $sut = new SystemClock();

        static::assertSame($sut->now()->getTimezone()->getName(), 'UTC');
    }
}
