<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Domain\ProductFileDropping\Write\Model;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Event\SupplierFileAdded;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Model\SupplierFile;
use PHPUnit\Framework\TestCase;

final class SupplierFileTest extends TestCase
{
    /** @test */
    public function itCreatesASupplierFileAndStoresASupplierFileAddedEvent(): void
    {
        $supplierFile = SupplierFile::create(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'supplier-file.xlsx',
            '2/f/a/4/2fa4afe5465afe5655supplier-file.xlsx',
            '44ce8069-8da1-4986-872f-311737f46f01',
            '44ce8069-8da1-4986-872f-311737f46f02',
        );
        $this->assertEquals('b8b13d0b-496b-4a7c-a574-0d522ba90752', $supplierFile->identifier());
        $this->assertEquals('supplier-file.xlsx', $supplierFile->filename());
        $this->assertEquals('2/f/a/4/2fa4afe5465afe5655supplier-file.xlsx', $supplierFile->path());
        $this->assertEquals('44ce8069-8da1-4986-872f-311737f46f01', $supplierFile->uploadedByContributor());
        $this->assertEquals('44ce8069-8da1-4986-872f-311737f46f02', $supplierFile->uploadedBySupplier());
        $this->assertIsString($supplierFile->uploadedAt());
        $this->assertNull($supplierFile->downloadedAt());

        $supplierFileEvents = $supplierFile->events();
        $this->assertCount(1, $supplierFileEvents);
        $this->assertInstanceOf(SupplierFileAdded::class, $supplierFileEvents[0]);
    }
}
