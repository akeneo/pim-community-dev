<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListProductFilesForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseListProductFilesForSupplierIntegration extends SqlIntegrationTestCase
{
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $suppplierRepository = $this->get(Repository::class);
        $suppplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7')
                ->withCode('supplier_1')
                ->withContributors(['contributor1@example.com', 'contributor2@example.com'])
                ->build(),
        );
        $suppplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('951c7717-8316-42e7-b053-61f265507178')
                ->withCode('supplier_2')
                ->build(),
        );

        $this->supplier = new Supplier(
            'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
            'supplier_1',
            'Supplier label',
        );
    }

    /** @test */
    public function itGetsNothingIfThereIsNoProductFilesForAGivenContributorAndTheContributorsBelongingToTheSameSupplier(): void
    {
        static::assertEmpty(($this->get(ListProductFilesForSupplier::class))('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7'));
    }


    /** @test */
    public function itGetsNoMoreThanTenFiveProductFilesAtATime(): void
    {
        $productFileRepository = $this->get(ProductFileRepository::class);
        for ($i = 0; 30 > $i; $i++) {
            $productFileRepository->save(
                (new ProductFileBuilder())
                    ->uploadedBySupplier($this->supplier)
                    ->withContributorEmail($i % 2 ? 'contributor1@example.com' : 'contributor2@example.com')
                    ->withOriginalFilename(sprintf('products_%d.xlsx', $i+1))
                    ->uploadedAt(
                        (new \DateTimeImmutable())->add(
                            \DateInterval::createFromDateString(sprintf('%d minutes', 30 - $i)),
                        ),
                    )
                    ->build(),
            );
        }

        $supplierProductFiles = ($this->get(ListProductFilesForSupplier::class))('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7');

        $expectedProductFilenames = [];
        for ($i = 0; ListProductFilesForSupplier::NUMBER_OF_PRODUCT_FILES_PER_PAGE > $i; $i++) {
            $expectedProductFilenames[] = sprintf('products_%d.xlsx', $i+1);
        }

        static::assertEqualsCanonicalizing(
            $expectedProductFilenames,
            array_map(
                fn (ProductFile $supplierProductFile) => $supplierProductFile->originalFilename,
                $supplierProductFiles,
            ),
        );
    }

    /** @test */
    public function itPaginatesTheProductFilesList(): void
    {
        $productFileRepository = $this->get(ProductFileRepository::class);
        for ($i = 0; 35 > $i; $i++) {
            $productFileRepository->save(
                (new ProductFileBuilder())
                    ->uploadedBySupplier($this->supplier)
                    ->withContributorEmail($i % 2 ? 'contributor1@example.com' : 'contributor2@example.com')
                    ->withOriginalFilename(sprintf('products_%d.xlsx', $i+1))
                    ->uploadedAt(
                        (new \DateTimeImmutable())->add(
                            \DateInterval::createFromDateString(sprintf('%d minutes', 30 - $i)),
                        ),
                    )
                    ->build(),
            );
        }

        $productFiles = ($this->get(ListProductFilesForSupplier::class))('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7', 4);

        static::assertCount(5, $productFiles);
    }

    /** @test */
    public function itSortsTheProductFilesListByUploadedDateDescending(): void
    {
        $productFileRepository = $this->get(ProductFileRepository::class);
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->uploadedBySupplier($this->supplier)
                ->withOriginalFilename('file1.xlsx')
                ->uploadedAt((new \DateTimeImmutable())->modify('-10 DAY'))
                ->build(),
        );
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->uploadedBySupplier($this->supplier)
                ->withOriginalFilename('file2.xlsx')
                ->build(),
        );
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->uploadedBySupplier($this->supplier)
                ->withOriginalFilename('file3.xlsx')
                ->uploadedAt((new \DateTimeImmutable())->modify('-2 DAY'))
                ->build(),
        );

        $productFiles = $this->get(ListProductFilesForSupplier::class)('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7');

        static::assertSame('file2.xlsx', $productFiles[0]->originalFilename);
        static::assertSame('file3.xlsx', $productFiles[1]->originalFilename);
        static::assertSame('file1.xlsx', $productFiles[2]->originalFilename);
    }

    /** @test */
    public function itGetsTheProductFilesWithTheirComments(): void
    {
        $productFile = (new ProductFileBuilder())
            ->withIdentifier('5d001a43-a42d-4083-8673-b64bb4ecd26f')
            ->uploadedBySupplier($this->supplier)
            ->uploadedAt(new \DateTimeImmutable('2022-09-07 08:54:38'))
            ->build();
        $productFile->addNewRetailerComment(
            'Your product file is awesome!',
            'julia@roberts.com',
            new \DateTimeImmutable('2022-09-07 00:00:00'),
        );
        $productFile->addNewSupplierComment(
            'Here are the products I\'ve got for you.',
            'jimmy@punchline.com',
            new \DateTimeImmutable('2022-09-07 00:00:01'),
        );
        ($this->get(ProductFileRepository::class))->save($productFile);

        $sut = $this->get(ListProductFilesForSupplier::class);
        /** @var ProductFile[] $productFiles */
        $productFiles = ($sut)('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7');

        static::assertCount(1, $productFiles);
        static::assertSame('5d001a43-a42d-4083-8673-b64bb4ecd26f', $productFiles[0]->identifier);
        static::assertSame('file.xlsx', $productFiles[0]->originalFilename);
        static::assertSame('path/to/file.xlsx', $productFiles[0]->path);
        static::assertSame('contributor@example.com', $productFiles[0]->uploadedByContributor);
        static::assertSame('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7', $productFiles[0]->uploadedBySupplier);
        static::assertSame('2022-09-07 08:54:38', $productFiles[0]->uploadedAt);
        static::assertCount(1, $productFiles[0]->retailerComments);
        static::assertCount(1, $productFiles[0]->supplierComments);
        static::assertEquals([[
            'content' => 'Your product file is awesome!' ,
            'author_email' => 'julia@roberts.com',
            'created_at' => '2022-09-07 00:00:00.000000',
        ]], $productFiles[0]->retailerComments);
        static::assertEquals([[
            'content' => 'Here are the products I\'ve got for you.' ,
            'author_email' => 'jimmy@punchline.com',
            'created_at' => '2022-09-07 00:00:01.000000',
        ]], $productFiles[0]->supplierComments);
    }
}
