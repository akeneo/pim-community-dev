<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileDropping\Write\Model;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event\ProductFileAdded;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use PHPUnit\Framework\TestCase;

final class ProductFileTest extends TestCase
{
    /** @test */
    public function itCreatesAProductFileAndStoresAProductFileAddedEvent(): void
    {
        $productFileIdentifier = Identifier::fromString('d06c58da-4cd7-469d-a3fc-37209a05e9e2');
        $productFile = ProductFile::create(
            (string) $productFileIdentifier,
            'supplier-file.xlsx',
            '2/f/a/4/2fa4afe5465afe5655/supplier-file.xlsx',
            'contributor@example.com',
            new Supplier(
                '44ce8069-8da1-4986-872f-311737f46f02',
                'los_pollos_hermanos',
                'Los Pollos Hermanos',
            ),
        );
        $this->assertEquals((string) $productFileIdentifier, $productFile->identifier());
        $this->assertEquals('supplier-file.xlsx', $productFile->originalFilename());
        $this->assertEquals('2/f/a/4/2fa4afe5465afe5655/supplier-file.xlsx', $productFile->path());
        $this->assertEquals('contributor@example.com', $productFile->contributorEmail());
        $this->assertEquals('Los Pollos Hermanos', $productFile->supplierLabel());
        $this->assertIsString($productFile->uploadedAt());
        $this->assertFalse($productFile->downloaded());

        $productFileEvents = $productFile->events();
        $this->assertCount(1, $productFileEvents);
        $this->assertInstanceOf(ProductFileAdded::class, $productFileEvents[0]);
    }
}
