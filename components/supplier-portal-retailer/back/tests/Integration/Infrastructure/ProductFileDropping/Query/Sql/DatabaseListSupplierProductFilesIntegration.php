<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListSupplierProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImport;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\ProductFileImportStatus;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\ProductFileImportRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Akeneo\SupplierPortal\Retailer\Test\Unit\Fakes\FrozenClock;
use Doctrine\DBAL\Connection;

final class DatabaseListSupplierProductFilesIntegration extends SqlIntegrationTestCase
{
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f00')
                ->withCode('supplier_1')
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
        static::assertCount(0, ($this->get(ListSupplierProductFiles::class))('44ce8069-8da1-4986-872f-311737f46f00'));
    }

    /** @test */
    public function itGetsOnlyTheProductFilesOfAGivenSupplier(): void
    {
        $supplierRepository = $this->get(Repository::class);

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
        for ($i = 1; 30 >= $i; $i++) {
            ($this->get(ProductFileRepository::class))->save(
                (new ProductFileBuilder())
                    ->uploadedBySupplier($this->supplier)
                    ->build(),
            );
        }

        static::assertCount(25, $this->get(ListSupplierProductFiles::class)('44ce8069-8da1-4986-872f-311737f46f00'));
    }

    /** @test */
    public function itPaginatesTheProductFilesList(): void
    {
        for ($i = 1; 30 >= $i; $i++) {
            ($this->get(ProductFileRepository::class))->save(
                (new ProductFileBuilder())
                    ->uploadedBySupplier($this->supplier)
                    ->build(),
            );
        }

        $productFiles = $this->get(ListSupplierProductFiles::class)('44ce8069-8da1-4986-872f-311737f46f00', 2);

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

        $productFileRepository = $this->get(ProductFileRepository::class);
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->uploadedBySupplier($this->supplier)
                ->withOriginalFilename('file1.xlsx')
                ->withContributorEmail('contributor@example.com')
                ->uploadedAt($file1Date)
                ->build(),
        );
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->uploadedBySupplier($this->supplier)
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

    /** @test */
    public function itReturnsHasUnreadCommentsProperty(): void
    {
        $productFileRepository = $this->get(ProductFileRepository::class);

        $productFileWithUnreadComments = (new ProductFileBuilder())
            ->withIdentifier('5d001a43-a42d-4083-8673-b64bb4ecd26f')
            ->uploadedAt((new FrozenClock('2022-11-29 15:05:38'))->now())
            ->uploadedBySupplier($this->supplier)
            ->build();
        $productFileWithUnreadComments->addNewSupplierComment(
            'Here are the products I\'ve got for you.',
            'jimmy@punchline.com',
            new \DateTimeImmutable('2022-10-26 00:00:00'),
        );
        $productFileRepository->save($productFileWithUnreadComments);

        $this->addLastReadAtByRetailer(
            '5d001a43-a42d-4083-8673-b64bb4ecd26f',
            new \DateTimeImmutable('2022-10-25 00:00:00'),
        );

        $productFileWithoutUnreadComments = (new ProductFileBuilder())
            ->withIdentifier('a3aac0e2-9eb9-4203-8af2-5425b2062ad4')
            ->uploadedAt((new FrozenClock('2022-11-29 14:05:38'))->now())
            ->uploadedBySupplier($this->supplier)
            ->build();
        $productFileWithoutUnreadComments->addNewSupplierComment(
            'Here is a read comment',
            'steve@job.com',
            new \DateTimeImmutable('2022-10-26 00:00:00'),
        );
        $productFileRepository->save($productFileWithoutUnreadComments);

        $this->addLastReadAtByRetailer(
            'a3aac0e2-9eb9-4203-8af2-5425b2062ad4',
            new \DateTimeImmutable('2022-10-27 00:00:00'),
        );

        $productFiles = $this->get(ListSupplierProductFiles::class)('44ce8069-8da1-4986-872f-311737f46f00');

        static::assertCount(2, $productFiles);
        static::assertTrue($productFiles[0]->hasUnreadComments);
        static::assertFalse($productFiles[1]->hasUnreadComments);
    }

    /** @test */
    public function itReturnsTrueForHasUnreadCommentsIfThereIsCommentsButNoLastReadAtDate(): void
    {
        $productFileRepository = $this->get(ProductFileRepository::class);

        $productFileWithUnreadComments = (new ProductFileBuilder())
            ->withIdentifier('5d001a43-a42d-4083-8673-b64bb4ecd26f')
            ->uploadedBySupplier($this->supplier)
            ->build();
        $productFileWithUnreadComments->addNewSupplierComment(
            'Here are the products I\'ve got for you.',
            'jimmy@punchline.com',
            new \DateTimeImmutable('2022-10-26 00:00:00'),
        );
        $productFileRepository->save($productFileWithUnreadComments);

        $productFiles = $this->get(ListSupplierProductFiles::class)('44ce8069-8da1-4986-872f-311737f46f00');

        static::assertTrue($productFiles[0]->hasUnreadComments);
    }

    /** @test */
    public function itReturnsFalseForHasUnreadCommentsIfThereIsNoCommentsButALastReadAtDate(): void
    {
        $productFileRepository = $this->get(ProductFileRepository::class);

        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withIdentifier('5d001a43-a42d-4083-8673-b64bb4ecd26f')
                ->uploadedBySupplier($this->supplier)
                ->build(),
        );
        $this->addLastReadAtByRetailer(
            '5d001a43-a42d-4083-8673-b64bb4ecd26f',
            new \DateTimeImmutable('2022-10-27 00:00:00'),
        );

        $productFiles = $this->get(ListSupplierProductFiles::class)('44ce8069-8da1-4986-872f-311737f46f00');

        static::assertFalse($productFiles[0]->hasUnreadComments);
    }

    /** @test */
    public function itReturnsFalseForHasUnreadCommentsIfThereIsNoCommentsNorLastReadAtDate(): void
    {
        ($this->get(ProductFileRepository::class))->save(
            (new ProductFileBuilder())
                ->uploadedBySupplier($this->supplier)
                ->build(),
        );

        $productFiles = $this->get(ListSupplierProductFiles::class)('44ce8069-8da1-4986-872f-311737f46f00');

        static::assertFalse($productFiles[0]->hasUnreadComments);
    }

    /** @test */
    public function itReturnsTheProductFileImportStatus(): void
    {
        $productFileRepository = $this->get(ProductFileRepository::class);
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->uploadedBySupplier($this->supplier)
                ->build(),
        );

        $productFiles = $this->get(ListSupplierProductFiles::class)('44ce8069-8da1-4986-872f-311737f46f00');

        static::assertSame(ProductFileImportStatus::TO_IMPORT->value, $productFiles[0]->importStatus);
    }

    /** @test */
    public function itReturnsTheProductFileImportStatusInProgress(): void
    {
        $productFile = (new ProductFileBuilder())
            ->uploadedBySupplier($this->supplier)
            ->build();
        $productFileRepository = $this->get(ProductFileRepository::class);
        $productFileRepository->save($productFile);

        $productFileImport = ProductFileImport::start($productFile, 666);
        ($this->get(ProductFileImportRepository::class))->save($productFileImport);

        $productFiles = $this->get(ListSupplierProductFiles::class)('44ce8069-8da1-4986-872f-311737f46f00');

        static::assertSame(ProductFileImportStatus::IN_PROGRESS->value, $productFiles[0]->importStatus);
    }

    /** @test */
    public function itCanSearchForProductFiles(): void
    {
        $productFileRepository = $this->get(ProductFileRepository::class);
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withOriginalFilename('file1.xlsx')
                ->uploadedBySupplier($this->supplier)
                ->build(),
        );
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withOriginalFilename('file2.xlsx')
                ->uploadedBySupplier($this->supplier)
                ->build(),
        );

        $productFiles = $this->get(ListSupplierProductFiles::class)('44ce8069-8da1-4986-872f-311737f46f00', 1, 'file1');
        static::assertCount(1, $productFiles);
        static::assertSame('file1.xlsx', $productFiles[0]->originalFilename);
    }

    /** @test */
    public function itCanSearchForProductFilesAndHandleThePagination(): void
    {
        for ($i = 0; 40 > $i; $i++) {
            ($this->get(ProductFileRepository::class))->save(
                (new ProductFileBuilder())
                    ->uploadedBySupplier($this->supplier)
                    ->withOriginalFilename('file.xlsx')
                    ->build(),
            );
        }
        for ($i = 0; 5 > $i; $i++) {
            ($this->get(ProductFileRepository::class))->save(
                (new ProductFileBuilder())
                    ->uploadedBySupplier($this->supplier)
                    ->withOriginalFilename('burger.xlsx')
                    ->build(),
            );
        }

        static::assertCount(15, $this->get(ListSupplierProductFiles::class)('44ce8069-8da1-4986-872f-311737f46f00', 2, 'file'));

        static::assertEmpty($this->get(ListSupplierProductFiles::class)('44ce8069-8da1-4986-872f-311737f46f00', 3, 'file'));
    }

    /** @test */
    public function itCanFilterProductFilesByStatus(): void
    {
        $productFileRepository = $this->get(ProductFileRepository::class);
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withIdentifier('5d001a43-a42d-4083-8673-b64bb4ecd26f')
                ->uploadedBySupplier($this->supplier)
                ->build(),
        );
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->uploadedBySupplier($this->supplier)
                ->build(),
        );
        $productFile = $productFileRepository->find(Identifier::fromString('5d001a43-a42d-4083-8673-b64bb4ecd26f'));
        $productFileImport = ProductFileImport::start($productFile, 666);
        $productFileImport->completedAt(new \DateTimeImmutable());
        ($this->get(ProductFileImportRepository::class))->save($productFileImport);

        $productFiles = $this->get(ListSupplierProductFiles::class)('44ce8069-8da1-4986-872f-311737f46f00', 1, '', ProductFileImportStatus::COMPLETED);

        static::assertCount(1, $productFiles);
        static::assertSame(ProductFileImportStatus::COMPLETED->value, $productFiles[0]->importStatus);
    }

    public function itReturnsAllStatusIfThereIsNoStatusToFilterOn(): void
    {
        $productFileRepository = $this->get(ProductFileRepository::class);
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withIdentifier('5d001a43-a42d-4083-8673-b64bb4ecd26f')
                ->uploadedBySupplier($this->supplier)
                ->build(),
        );
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->uploadedBySupplier($this->supplier)
                ->build(),
        );
        $productFile = $productFileRepository->find(Identifier::fromString('5d001a43-a42d-4083-8673-b64bb4ecd26f'));
        $productFileImport = ProductFileImport::start($productFile, 666);
        $productFileImport->completedAt(new \DateTimeImmutable());
        ($this->get(ProductFileImportRepository::class))->save($productFileImport);

        $productFiles = $this->get(ListSupplierProductFiles::class)('44ce8069-8da1-4986-872f-311737f46f00', 1);

        static::assertCount(2, $productFiles);
        static::assertSame(ProductFileImportStatus::COMPLETED->value, $productFiles[0]->importStatus);
        static::assertSame(ProductFileImportStatus::TO_IMPORT->value, $productFiles[1]->importStatus);
    }

    private function addLastReadAtByRetailer(string $productFileIdentifier, \DateTimeImmutable $lastReadAt): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_product_file_comments_read_by_retailer` (
                product_file_identifier, last_read_at                
            ) VALUES (:productFileIdentifier, :lastReadAt);
        SQL;

        $this->get(Connection::class)->executeStatement(
            $sql,
            [
                'productFileIdentifier' => $productFileIdentifier,
                'lastReadAt' => $lastReadAt->format('Y-m-d H:i:s'),
            ],
        );
    }
}
