<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileDropping\Write\Event;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event\ProductFileDeleted;
use PHPUnit\Framework\TestCase;

final class ProductFileDeletedTest extends TestCase
{
    /** @test */
    public function itExposesTheProductFileIdentifier(): void
    {
        $productFileDeleted = new ProductFileDeleted('1d16a322-4c4f-4e2d-8b25-c18b0bfe99b0');

        static::assertSame('1d16a322-4c4f-4e2d-8b25-c18b0bfe99b0', $productFileDeleted->productFileIdentifier);
    }
}
