<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListSupplierProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseListSupplierProductFilesIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturnsAnEmptyArrayIfThereIsNoFile(): void
    {
        static::assertCount(0, ($this->get(ListSupplierProductFiles::class))('44ce8069-8da1-4986-872f-311737f46f00'));
    }

    /** @test */
    public function itGetsOnlyTheProductFilesOfAGivenSupplier(): void
    {
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

        static::assertCount(15, $this->get(ListSupplierProductFiles::class)('44ce8069-8da1-4986-872f-311737f46f00'));
    }

    /** @test */
    public function itGetsNoMoreThanTwentyFiveProductFilesAtATime(): void
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
        for ($i = 1; 30 >= $i; $i++) {
            ($this->get(ProductFileRepository::class))->save(
                (new ProductFileBuilder())
                    ->uploadedBySupplier($supplier)
                    ->build(),
            );
        }

        static::assertCount(25, $this->get(ListSupplierProductFiles::class)('44ce8069-8da1-4986-872f-311737f46f00'));
    }

    /** @test */
    public function itPaginatesTheProductFilesList(): void
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
        for ($i = 1; 30 >= $i; $i++) {
            ($this->get(ProductFileRepository::class))->save(
                (new ProductFileBuilder())
                    ->uploadedBySupplier($supplier)
                    ->build(),
            );
        }

        $productFiles = $this->get(ListSupplierProductFiles::class)('44ce8069-8da1-4986-872f-311737f46f00', 2);

        static::assertCount(5, $productFiles);
    }

    /** @test */
    public function itSortsTheProductFilesListByUploadedDateDescending(): void
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
                ->uploadedBySupplier($supplier)
                ->withOriginalFilename('file1.xlsx')
                ->uploadedAt((new \DateTimeImmutable())->modify('-10 DAY'))
                ->build(),
        );
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->uploadedBySupplier($supplier)
                ->withOriginalFilename('file2.xlsx')
                ->build(),
        );
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->uploadedBySupplier($supplier)
                ->withOriginalFilename('file3.xlsx')
                ->uploadedAt((new \DateTimeImmutable())->modify('-2 DAY'))
                ->build(),
        );

        $productFiles = $this->get(ListSupplierProductFiles::class)('44ce8069-8da1-4986-872f-311737f46f00');

        static::assertSame('file2.xlsx', $productFiles[0]->originalFilename);
        static::assertSame('file3.xlsx', $productFiles[1]->originalFilename);
        static::assertSame('file1.xlsx', $productFiles[2]->originalFilename);
    }

    /** @test */
    public function itReturnsAnArrayOfReadModels(): void
    {
        $file1Date = new \DateTimeImmutable();
        $file2Date = (new \DateTimeImmutable())->modify('-2 DAY');

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
                ->uploadedBySupplier($supplier)
                ->withOriginalFilename('file1.xlsx')
                ->withContributorEmail('contributor@example.com')
                ->uploadedAt($file1Date)
                ->build(),
        );
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->uploadedBySupplier($supplier)
                ->withOriginalFilename('file2.xlsx')
                ->uploadedAt($file2Date)
                ->build(),
        );

        $productFiles = $this->get(ListSupplierProductFiles::class)('44ce8069-8da1-4986-872f-311737f46f00');

        static::assertCount(2, $productFiles);
        static::assertSame('file1.xlsx', $productFiles[0]->originalFilename);
        static::assertSame('contributor@example.com', $productFiles[0]->uploadedByContributor);
        static::assertSame($file1Date->format('Y-m-d H:i:s'), $productFiles[0]->uploadedAt);
    }
}
