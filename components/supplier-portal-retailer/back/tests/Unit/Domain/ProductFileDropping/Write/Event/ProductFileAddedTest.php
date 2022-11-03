<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileDropping\Write\Event;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event\ProductFileAdded;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use PHPUnit\Framework\TestCase;

final class ProductFileAddedTest extends TestCase
{
    /** @test */
    public function itExposesTheContributorEmailAndTheSupplierLabel(): void
    {
        $productFileAdded = new ProductFileAdded(
            (new ProductFileBuilder())
                ->withIdentifier('1d16a322-4c4f-4e2d-8b25-c18b0bfe99b0')
                ->withContributorEmail('jimmy@supplier.com')
                ->uploadedBySupplier(
                    new Supplier(
                        'f7555f61-2ea6-4b0e-88f2-737e504e7b95',
                        'supplier_code',
                        'Supplier label',
                    ),
                )
                ->build(),
            'Supplier label',
        );

        static::assertSame('jimmy@supplier.com', $productFileAdded->contributorEmail());
        static::assertSame('Supplier label', $productFileAdded->supplierLabel());
    }
}
