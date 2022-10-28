<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseListProductFilesIntegration extends SqlIntegrationTestCase
{
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f00')
                ->build(),
        );

        $this->supplier = new Supplier(
            '44ce8069-8da1-4986-872f-311737f46f00',
            'supplier_code',
            'Supplier label',
        );
    }
    /** @test */
    public function itReturnsAnEmptyArrayIfThereIsNoFile(): void
    {
        static::assertCount(0, ($this->get(ListProductFiles::class))());
    }

    /** @test */
    public function itGetsNoMoreThanTwentyFiveProductFilesAtATime(): void
    {
        for ($i = 1; 30 >= $i; $i++) {
            $this->get(ProductFileRepository::class)->save(
                (new ProductFileBuilder())
                    ->uploadedBySupplier($this->supplier)
                    ->build(),
            );
        }

        static::assertCount(25, $this->get(ListProductFiles::class)());
    }

    /** @test */
    public function itPaginatesTheProductFilesList(): void
    {
        for ($i = 1; 30 >= $i; $i++) {
            $this->get(ProductFileRepository::class)->save(
                (new ProductFileBuilder())
                    ->uploadedBySupplier($this->supplier)
                    ->build(),
            );
        }

        $productFiles = $this->get(ListProductFiles::class)(2);

        static::assertCount(5, $productFiles);
    }

    /** @test */
    public function itSortsTheProductFilesListByUploadedDateDescending(): void
    {
        $productFileRepository = $this->get(ProductFileRepository::class);
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withOriginalFilename('file2.xlsx')
                ->uploadedBySupplier($this->supplier)
                ->uploadedAt(new \DateTimeImmutable())
                ->build(),
        );

        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withOriginalFilename('file3.xlsx')
                ->uploadedBySupplier($this->supplier)
                ->uploadedAt((new \DateTimeImmutable())->modify('-2 DAY'))
                ->build(),
        );

        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withOriginalFilename('file1.xlsx')
                ->uploadedBySupplier($this->supplier)
                ->uploadedAt((new \DateTimeImmutable())->modify('-10 DAY'))
                ->build(),
        );

        $productFiles = $this->get(ListProductFiles::class)();

        static::assertSame('file2.xlsx', $productFiles[0]->originalFilename);
        static::assertSame('file3.xlsx', $productFiles[1]->originalFilename);
        static::assertSame('file1.xlsx', $productFiles[2]->originalFilename);
    }

    /** @test */
    public function itReturnsAnArrayOfReadModels(): void
    {
        $file1Date = new \DateTimeImmutable();
        $file2Date = (new \DateTimeImmutable())->modify('-2 DAY');

        $productFileRepository = $this->get(ProductFileRepository::class);
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withOriginalFilename('file1.xlsx')
                ->uploadedBySupplier($this->supplier)
                ->withContributorEmail('contributor@megasupplier.com')
                ->uploadedAt($file1Date)
                ->build(),
        );

        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withOriginalFilename('file2.xlsx')
                ->uploadedBySupplier($this->supplier)
                ->uploadedAt($file2Date)
                ->build(),
        );

        $productFiles = $this->get(ListProductFiles::class)();

        static::assertSame('file1.xlsx', $productFiles[0]->originalFilename);
        static::assertSame('contributor@megasupplier.com', $productFiles[0]->uploadedByContributor);
        static::assertSame('Supplier label', $productFiles[0]->uploadedBySupplier);
        static::assertSame($file1Date->format('Y-m-d H:i:s'), $productFiles[0]->uploadedAt);
    }
}
