<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetSupplierProductFilesCount;
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

final class DatabaseGetSupplierProductFilesCountIntegration extends SqlIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $supplierRepository = $this->get(Repository::class);
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f00')
                ->withCode('supplier_1')
                ->build(),
        );
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('a20576cd-840f-4124-9900-14d581491387')
                ->withCode('supplier_2')
                ->build(),
        );

        $productFileRepository = $this->get(ProductFileRepository::class);
        $supplierOne = new Supplier(
            '44ce8069-8da1-4986-872f-311737f46f00',
            'supplier_1',
            'Supplier 1 label',
        );
        for ($i = 1; 15 >= $i; $i++) {
            $productFileRepository->save(
                (new ProductFileBuilder())
                    ->uploadedBySupplier($supplierOne)
                    ->withOriginalFilename('file'.$i)
                    ->build(),
            );
        }

        $supplierTwo = new Supplier(
            'a20576cd-840f-4124-9900-14d581491387',
            'supplier_2',
            'Supplier 2 label',
        );
        for ($i = 1; 10 >= $i; $i++) {
            $productFileRepository->save(
                (new ProductFileBuilder())
                    ->uploadedBySupplier($supplierTwo)
                    ->build(),
            );
        }
    }

    /** @test */
    public function itReturns0IfThereIsNoFile(): void
    {
        static::assertSame(0, $this->get(GetSupplierProductFilesCount::class)('44ce8069-8da1-4986-872f-311737f46f01'));
    }

    /** @test */
    public function itReturnsTheNumberOfProductFilesForASupplierWithoutSearch(): void
    {
        static::assertSame(15, $this->get(GetSupplierProductFilesCount::class)('44ce8069-8da1-4986-872f-311737f46f00'));
    }

    /** @test */
    public function itReturnsTheNumberOfProductFilesForASupplierWithSearch(): void
    {
        static::assertSame(7, $this->get(GetSupplierProductFilesCount::class)('44ce8069-8da1-4986-872f-311737f46f00', '1'));
    }

    /** @test */
    public function itCanCountProductFilesDependingOnStatus(): void
    {
        $supplierRepository = $this->get(Repository::class);
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('14ce8069-8da1-4986-872f-311737f46f00')
                ->withCode('supplier_code_1')
                ->build(),
        );
        $supplier1 = new Supplier(
            '14ce8069-8da1-4986-872f-311737f46f00',
            'supplier_code_1',
            'Supplier label',
        );

        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('24ce8069-8da1-4986-872f-311737f46f00')
                ->withCode('supplier_code_2')
                ->build(),
        );
        $supplier2 = new Supplier(
            '24ce8069-8da1-4986-872f-311737f46f00',
            'supplier_code_2',
            'Supplier label',
        );

        $productFileRepository = $this->get(ProductFileRepository::class);
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->uploadedBySupplier($supplier1)
                ->build(),
        );

        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withIdentifier('1d001a43-a42d-4083-8673-b64bb4ecd26f')
                ->uploadedBySupplier($supplier2)
                ->build(),
        );
        $productFile1 = $productFileRepository->find(Identifier::fromString('1d001a43-a42d-4083-8673-b64bb4ecd26f'));
        $productFileImport1 = ProductFileImport::start($productFile1, 1);
        ($this->get(ProductFileImportRepository::class))->save($productFileImport1);

        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withIdentifier('2d001a43-a42d-4083-8673-b64bb4ecd26f')
                ->uploadedBySupplier($supplier1)
                ->build(),
        );
        $productFile2 = $productFileRepository->find(Identifier::fromString('2d001a43-a42d-4083-8673-b64bb4ecd26f'));
        $productFileImport2 = ProductFileImport::start($productFile2, 2);
        $productFileImport2->completedAt(new \DateTimeImmutable());
        ($this->get(ProductFileImportRepository::class))->save($productFileImport2);

        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withIdentifier('3d001a43-a42d-4083-8673-b64bb4ecd26f')
                ->uploadedBySupplier($supplier2)
                ->build(),
        );
        $productFile3 = $productFileRepository->find(Identifier::fromString('3d001a43-a42d-4083-8673-b64bb4ecd26f'));
        $productFileImport3 = ProductFileImport::start($productFile3, 3);
        $productFileImport3->failedAt(new \DateTimeImmutable());
        ($this->get(ProductFileImportRepository::class))->save($productFileImport3);

        static::assertSame(
            1,
            $this->get(GetSupplierProductFilesCount::class)(
                '14ce8069-8da1-4986-872f-311737f46f00',
                '',
                ProductFileImportStatus::TO_IMPORT
            )
        );
        static::assertSame(
            1,
            $this->get(GetSupplierProductFilesCount::class)(
                '24ce8069-8da1-4986-872f-311737f46f00',
                '',
                ProductFileImportStatus::IN_PROGRESS
            )
        );
        static::assertSame(
            1,
            $this->get(GetSupplierProductFilesCount::class)(
                '14ce8069-8da1-4986-872f-311737f46f00',
                '',
                ProductFileImportStatus::COMPLETED
            )
        );
        static::assertSame(
            1,
            $this->get(GetSupplierProductFilesCount::class)(
                '24ce8069-8da1-4986-872f-311737f46f00',
                '',
                ProductFileImportStatus::FAILED
            )
        );
        static::assertSame(2, $this->get(GetSupplierProductFilesCount::class)('14ce8069-8da1-4986-872f-311737f46f00'));
        static::assertSame(2, $this->get(GetSupplierProductFilesCount::class)('24ce8069-8da1-4986-872f-311737f46f00'));
    }
}
