<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileDropping\Read\Event;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Event\ProductFileDownloaded;
use PHPUnit\Framework\TestCase;

final class ProductFileDownloadedTest extends TestCase
{
    /** @test */
    public function itContainsTheProductFileIdentifierAndTheUserId(): void
    {
        $productFileDownloaded = new ProductFileDownloaded(
            '05582bd2-5273-4e96-8c1d-0676e372674e',
            1,
        );

        static::assertSame(
            '05582bd2-5273-4e96-8c1d-0676e372674e',
            $productFileDownloaded->productFileIdentifier,
        );
        static::assertSame(
            1,
            $productFileDownloaded->userId,
        );
    }
}
