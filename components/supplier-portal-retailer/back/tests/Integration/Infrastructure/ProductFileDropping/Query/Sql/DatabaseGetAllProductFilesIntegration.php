<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetAllProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseGetAllProductFilesIntegration extends SqlIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f00')
                ->build(),
        );
    }
    /** @test */
    public function itReturnsAnEmptyArrayIfThereIsNoFile(): void
    {
        static::assertCount(0, ($this->get(GetAllProductFiles::class))());
    }

    /** @test */
    public function itGetsNoMoreThanTwentyFiveProductFilesAtATime(): void
    {
        for ($i = 1; 30 >= $i; $i++) {
            $this->get(ProductFileRepository::class)->save(
                (new ProductFileBuilder())
                    ->withUploadedBySupplier('44ce8069-8da1-4986-872f-311737f46f00')
                    ->build(),
            );
        }

        static::assertCount(25, $this->get(GetAllProductFiles::class)());
    }

    /** @test */
    public function itPaginatesTheProductFilesList(): void
    {
        for ($i = 1; 30 >= $i; $i++) {
            $this->get(ProductFileRepository::class)->save(
                (new ProductFileBuilder())
                    ->withUploadedBySupplier('44ce8069-8da1-4986-872f-311737f46f00')
                    ->build(),
            );
        }

        $productFiles = $this->get(GetAllProductFiles::class)(2);

        static::assertCount(5, $productFiles);
    }

    /** @test */
    public function itSortsTheProductFilesListByUploadedDateDescending(): void
    {
        $productFileRepository = $this->get(ProductFileRepository::class);
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withOriginalFilename('file2.xlsx')
                ->withUploadedBySupplier('44ce8069-8da1-4986-872f-311737f46f00')
                ->withUploadedAt(new \DateTimeImmutable())
                ->build(),
        );

        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withOriginalFilename('file3.xlsx')
                ->withUploadedBySupplier('44ce8069-8da1-4986-872f-311737f46f00')
                ->withUploadedAt((new \DateTimeImmutable())->modify('-2 DAY'))
                ->build(),
        );

        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withOriginalFilename('file1.xlsx')
                ->withUploadedBySupplier('44ce8069-8da1-4986-872f-311737f46f00')
                ->withUploadedAt((new \DateTimeImmutable())->modify('-10 DAY'))
                ->build(),
        );

        $productFiles = $this->get(GetAllProductFiles::class)();

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
                ->withUploadedBySupplier('44ce8069-8da1-4986-872f-311737f46f00')
                ->withUploadedAt($file1Date)
                ->build(),
        );

        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withOriginalFilename('file2.xlsx')
                ->withUploadedBySupplier('44ce8069-8da1-4986-872f-311737f46f00')
                ->withUploadedAt($file2Date)
                ->build(),
        );

        $productFiles = $this->get(GetAllProductFiles::class)();

        static::assertSame('file1.xlsx', $productFiles[0]->originalFilename);
        static::assertSame('contributor@example.com', $productFiles[0]->uploadedByContributor);
        static::assertSame('Supplier label', $productFiles[0]->uploadedBySupplier);
        static::assertSame($file1Date->format('Y-m-d H:i:s'), $productFiles[0]->uploadedAt);
    }
}
