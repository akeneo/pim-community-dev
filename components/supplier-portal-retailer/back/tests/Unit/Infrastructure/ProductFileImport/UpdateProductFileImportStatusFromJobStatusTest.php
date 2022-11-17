<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\ProductFileImport;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImport;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImportStatus;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileImport\Repository\InMemory\InMemoryRepository;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileImport\UpdateProductFileImportStatusFromJobStatus;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use PHPUnit\Framework\TestCase;

final class UpdateProductFileImportStatusFromJobStatusTest extends TestCase
{
    /** @test */
    public function itDoesNotUpdateStatusIfProductFileImportIsNull(): void
    {
        $inMemoryProductFileImportRepository = new InMemoryRepository();
        $productFile = (new ProductFileBuilder())
            ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f02')
            ->build();

        $inMemoryProductFileImportRepository->save(ProductFileImport::start($productFile, 666));
        $productFileImport = $inMemoryProductFileImportRepository->find('44ce8069-8da1-4986-872f-311737f46f02');
        $this->assertSame(ProductFileImportStatus::IN_PROGRESS->value, $productFileImport->fileImportStatus());
        $this->assertNull($productFileImport->finishedAt());

        $sut = new UpdateProductFileImportStatusFromJobStatus($inMemoryProductFileImportRepository);
        ($sut)(BatchStatus::FAILED, 42);

        $this->assertSame(ProductFileImportStatus::IN_PROGRESS->value, $productFileImport->fileImportStatus());
        $this->assertNull($productFileImport->finishedAt());
    }

    /** @test */
    public function itUpdatesProductFileImportStatusToFailed(): void
    {
        $inMemoryProductFileImportRepository = new InMemoryRepository();
        $productFile = (new ProductFileBuilder())
            ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f02')
            ->build();

        $inMemoryProductFileImportRepository->save(ProductFileImport::start($productFile, 666));

        $sut = new UpdateProductFileImportStatusFromJobStatus($inMemoryProductFileImportRepository);
        ($sut)(BatchStatus::FAILED, 666);

        $productFileImport = $inMemoryProductFileImportRepository->find('44ce8069-8da1-4986-872f-311737f46f02');

        $this->assertSame(ProductFileImportStatus::FAILED->value, $productFileImport->fileImportStatus());
        $this->assertNotNull($productFileImport->finishedAt());
    }

    /** @test */
    public function itUpdatesProductFileImportStatusToCompleted(): void
    {
        $inMemoryProductFileImportRepository = new InMemoryRepository();
        $productFile = (new ProductFileBuilder())
            ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f02')
            ->build();

        $inMemoryProductFileImportRepository->save(ProductFileImport::start($productFile, 666));

        $sut = new UpdateProductFileImportStatusFromJobStatus($inMemoryProductFileImportRepository);
        ($sut)(BatchStatus::COMPLETED, 666);

        $productFileImport = $inMemoryProductFileImportRepository->find('44ce8069-8da1-4986-872f-311737f46f02');

        $this->assertSame(ProductFileImportStatus::COMPLETED->value, $productFileImport->fileImportStatus());
        $this->assertNotNull($productFileImport->finishedAt());
    }
}
