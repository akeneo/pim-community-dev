<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileDropping\Write\Model;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event\ProductFileAdded;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use PHPUnit\Framework\TestCase;

final class SupplierFileTest extends TestCase
{
    /** @test */
    public function itCreatesASupplierFileAndStoresASupplierFileAddedEvent(): void
    {
        $supplierFileIdentifier = Identifier::fromString('d06c58da-4cd7-469d-a3fc-37209a05e9e2');
        $supplierFile = ProductFile::create(
            (string) $supplierFileIdentifier,
            'supplier-file.xlsx',
            '2/f/a/4/2fa4afe5465afe5655/supplier-file.xlsx',
            'contributor@example.com',
            new Supplier(
                '44ce8069-8da1-4986-872f-311737f46f02',
                'los_pollos_hermanos',
                'Los Pollos Hermanos',
            ),
        );
        $this->assertEquals((string) $supplierFileIdentifier, $supplierFile->identifier());
        $this->assertEquals('supplier-file.xlsx', $supplierFile->originalFilename());
        $this->assertEquals('2/f/a/4/2fa4afe5465afe5655/supplier-file.xlsx', $supplierFile->path());
        $this->assertEquals('contributor@example.com', $supplierFile->contributorEmail());
        $this->assertEquals('Los Pollos Hermanos', $supplierFile->supplierLabel());
        $this->assertIsString($supplierFile->uploadedAt());
        $this->assertFalse($supplierFile->downloaded());

        $supplierFileEvents = $supplierFile->events();
        $this->assertCount(1, $supplierFileEvents);
        $this->assertInstanceOf(ProductFileAdded::class, $supplierFileEvents[0]);
    }
}
