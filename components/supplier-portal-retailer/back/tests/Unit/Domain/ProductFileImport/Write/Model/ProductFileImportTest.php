<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileImport\Write\Model;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImport;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImportStatus;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Unit\Fakes\FrozenClock;
use PHPUnit\Framework\TestCase;

class ProductFileImportTest extends TestCase
{
    /** @test */
    public function itCreatesAProductFileImportWithFileImportStatusInProgress(): void
    {
        $productFile = (new ProductFileBuilder)
            ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f02')
            ->build();
        $productFileImport = ProductFileImport::start($productFile, 666);

        $this->assertSame(ProductFileImportStatus::IN_PROGRESS->value, $productFileImport->fileImportStatus());
        $this->assertSame('44ce8069-8da1-4986-872f-311737f46f02', $productFileImport->productFileIdentifier());
        $this->assertSame(666, $productFileImport->importExecutionId());
        $this->assertNull($productFileImport->finishedAt());
    }

    /** @test */
    public function itUpdatesAProductFileImportWithFileImportStatusCompleted(): void
    {
        $productFile = (new ProductFileBuilder)
            ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f02')
            ->build();
        $productFileImport = ProductFileImport::start($productFile, 666);

        $this->assertNull($productFileImport->finishedAt());
        $this->assertSame(ProductFileImportStatus::IN_PROGRESS->value, $productFileImport->fileImportStatus());

        $completedAt = (new FrozenClock('2022-09-07 08:54:38'))->now();
        $productFileImport->completedAt($completedAt);

        $this->assertSame(ProductFileImportStatus::COMPLETED->value, $productFileImport->fileImportStatus());
        $this->assertSame('2022-09-07 08:54:38', $productFileImport->finishedAt());
    }

    /** @test */
    public function itUpdatesAProductFileImportWithFileImportStatusFailed(): void
    {
        $productFile = (new ProductFileBuilder)
            ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f02')
            ->build();
        $productFileImport = ProductFileImport::start($productFile, 666);

        $this->assertNull($productFileImport->finishedAt());
        $this->assertSame(ProductFileImportStatus::IN_PROGRESS->value, $productFileImport->fileImportStatus());

        $failedAt = (new FrozenClock('2022-09-07 08:54:38'))->now();
        $productFileImport->failedAt($failedAt);

        $this->assertSame(ProductFileImportStatus::FAILED->value, $productFileImport->fileImportStatus());
        $this->assertSame('2022-09-07 08:54:38', $productFileImport->finishedAt());
    }

    /** @test */
    public function itHydratesAProductFileImport(): void
    {
        $productFileIdentifier = '44ce8069-8da1-4986-872f-311737f46f02';
        $jobExecutionId = 666;
        $importStatus = ProductFileImportStatus::COMPLETED->value;

        $productFileImport = ProductFileImport::hydrate(
            $productFileIdentifier,
            $jobExecutionId,
            $importStatus,
            (new FrozenClock('2022-09-07 08:54:38'))->now(),
        );

        $this->assertSame($productFileIdentifier, $productFileImport->productFileIdentifier());
        $this->assertSame($jobExecutionId, $productFileImport->importExecutionId());
        $this->assertSame(ProductFileImportStatus::COMPLETED->value, $productFileImport->fileImportStatus());
        $this->assertSame('2022-09-07 08:54:38', $productFileImport->finishedAt());
    }
}
