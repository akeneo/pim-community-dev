<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileImport\Write\Model;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImport;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImportStatus;
use PHPUnit\Framework\TestCase;

class ProductFileImportTest extends TestCase
{
    /** @test */
    public function itCreateProductFileImportWithFileImportStatusInProgress(): void
    {
        $productFileImport = ProductFileImport::start('44ce8069-8da1-4986-872f-311737f46f02', 666);

        $this->assertSame(ProductFileImportStatus::IN_PROGRESS, $productFileImport->fileImportStatus());
        $this->assertSame('44ce8069-8da1-4986-872f-311737f46f02', $productFileImport->productFileIdentifier());
        $this->assertSame(666, $productFileImport->importExecutionId());
    }
}
