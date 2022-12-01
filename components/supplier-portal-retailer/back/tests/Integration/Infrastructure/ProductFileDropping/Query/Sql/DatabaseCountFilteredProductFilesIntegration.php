<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\CountFilteredProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\ProductFileImportStatus;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImport;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\ProductFileImportRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

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
                ->withIdentifier('0d001a43-a42d-4083-8673-b64bb4ecd26f')
                ->uploadedBySupplier($supplier)
                ->build(),
        );

        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withIdentifier('1d001a43-a42d-4083-8673-b64bb4ecd26f')
                ->uploadedBySupplier($supplier)
                ->build(),
        );
        $productFile1 = $productFileRepository->find(Identifier::fromString('1d001a43-a42d-4083-8673-b64bb4ecd26f'));
        $productFileImport1 = ProductFileImport::start($productFile1, 1);
        ($this->get(ProductFileImportRepository::class))->save($productFileImport1);

        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withIdentifier('2d001a43-a42d-4083-8673-b64bb4ecd26f')
                ->uploadedBySupplier($supplier)
                ->build(),
        );
        $productFile2 = $productFileRepository->find(Identifier::fromString('2d001a43-a42d-4083-8673-b64bb4ecd26f'));
        $productFileImport2 = ProductFileImport::start($productFile2, 2);
        $productFileImport2->completedAt(new \DateTimeImmutable());
        ($this->get(ProductFileImportRepository::class))->save($productFileImport2);

        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withIdentifier('3d001a43-a42d-4083-8673-b64bb4ecd26f')
                ->uploadedBySupplier($supplier)
                ->build(),
        );
        $productFile3 = $productFileRepository->find(Identifier::fromString('3d001a43-a42d-4083-8673-b64bb4ecd26f'));
        $productFileImport3 = ProductFileImport::start($productFile3, 3);
        $productFileImport3->failedAt(new \DateTimeImmutable());
        ($this->get(ProductFileImportRepository::class))->save($productFileImport3);

        static::assertSame(1, $this->get(CountFilteredProductFiles::class)('', ProductFileImportStatus::TO_IMPORT));
        static::assertSame(1, $this->get(CountFilteredProductFiles::class)('', ProductFileImportStatus::IN_PROGRESS));
        static::assertSame(1, $this->get(CountFilteredProductFiles::class)('', ProductFileImportStatus::COMPLETED));
        static::assertSame(1, $this->get(CountFilteredProductFiles::class)('', ProductFileImportStatus::FAILED));
        static::assertSame(4, $this->get(CountFilteredProductFiles::class)());
    }
}
