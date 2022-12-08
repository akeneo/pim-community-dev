<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\CountFilteredProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\ProductFileImportStatus;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\ProductFileImportRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileImportBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Ramsey\Uuid\Uuid;

final class DatabaseCountFilteredProductFilesIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturns0IfThereIsNoFile(): void
    {
        static::assertSame(0, $this->get(CountFilteredProductFiles::class)());
    }

    /** @test */
    public function itCanCountFilteredProductFiles(): void
    {
        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f00')
                ->build(),
        );

        $supplier = new Supplier(
            '44ce8069-8da1-4986-872f-311737f46f00',
            'supplier_code',
            'Supplier label',
        );

        $productFileRepository = $this->get(ProductFileRepository::class);
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withOriginalFilename('file1.xlsx')
                ->uploadedBySupplier($supplier)
                ->build(),
        );
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withOriginalFilename('file2.xlsx')
                ->uploadedBySupplier($supplier)
                ->build(),
        );

        static::assertSame(1, $this->get(CountFilteredProductFiles::class)('file1'));
        static::assertSame(0, $this->get(CountFilteredProductFiles::class)('file3'));
    }

    /** @test */
    public function itCanCountProductFilesDependingOnStatus(): void
    {
        $supplierIdentifier = Uuid::uuid4()->toString();
        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withIdentifier($supplierIdentifier)
                ->build(),
        );
        $supplier = new Supplier(
            $supplierIdentifier,
            'supplier_code',
            'Supplier label',
        );

        $productFileRepository = $this->get(ProductFileRepository::class);
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->uploadedBySupplier($supplier)
                ->build(),
        );

        $inProgressProductFile = (new ProductFileBuilder())
            ->uploadedBySupplier($supplier)
            ->build();
        $productFileRepository->save($inProgressProductFile);
        ($this->get(ProductFileImportRepository::class))->save(
            (new ProductFileImportBuilder())
                ->withProductFile($inProgressProductFile)
                ->withImportExecutionId(1)
                ->withImportStatus(ProductFileImportStatus::IN_PROGRESS)
                ->build(),
        );

        $competedProductFile = (new ProductFileBuilder())
            ->uploadedBySupplier($supplier)
            ->build();
        $productFileRepository->save($competedProductFile);

        ($this->get(ProductFileImportRepository::class))->save(
            (new ProductFileImportBuilder())
                ->withProductFile($competedProductFile)
                ->withImportExecutionId(2)
                ->withImportStatus(ProductFileImportStatus::COMPLETED)
                ->build(),
        );

        $failedProductFile = (new ProductFileBuilder())
            ->uploadedBySupplier($supplier)
            ->build();
        $productFileRepository->save($failedProductFile);

        ($this->get(ProductFileImportRepository::class))->save(
            (new ProductFileImportBuilder())
                ->withProductFile($failedProductFile)
                ->withImportExecutionId(3)
                ->withImportStatus(ProductFileImportStatus::FAILED)
                ->build(),
        );

        static::assertSame(1, $this->get(CountFilteredProductFiles::class)('', ProductFileImportStatus::TO_IMPORT));
        static::assertSame(1, $this->get(CountFilteredProductFiles::class)('', ProductFileImportStatus::IN_PROGRESS));
        static::assertSame(1, $this->get(CountFilteredProductFiles::class)('', ProductFileImportStatus::COMPLETED));
        static::assertSame(1, $this->get(CountFilteredProductFiles::class)('', ProductFileImportStatus::FAILED));
        static::assertSame(4, $this->get(CountFilteredProductFiles::class)());
    }
}
