<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetAllProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builders\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

final class DatabaseGetAllProductFilesIntegration extends SqlIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f00')
                ->withCode('supplier_1')
                ->withLabel('Supplier 1')
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
            $this->createProductFile('file.xlsx', new \DateTimeImmutable());
        }

        static::assertCount(25, $this->get(GetAllProductFiles::class)());
    }

    /** @test */
    public function itPaginatesTheProductFilesList(): void
    {
        for ($i = 1; 30 >= $i; $i++) {
            $this->createProductFile('file.xlsx', new \DateTimeImmutable());
        }

        $productFiles = $this->get(GetAllProductFiles::class)(2);

        static::assertCount(5, $productFiles);
    }

    /** @test */
    public function itSortsTheProductFilesListByUploadedDateDescending(): void
    {
        $this->createProductFile('file1.xlsx', (new \DateTimeImmutable())->modify('-10 DAY'));
        $this->createProductFile('file2.xlsx', new \DateTimeImmutable());
        $this->createProductFile('file3.xlsx', (new \DateTimeImmutable())->modify('-2 DAY'));

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

        $this->createProductFile('file1.xlsx', $file1Date);
        $this->createProductFile('file2.xlsx', $file2Date);

        $productFiles = $this->get(GetAllProductFiles::class)();

        static::assertSame('file1.xlsx', $productFiles[0]->originalFilename);
        static::assertSame('contributor@megasupplier.com', $productFiles[0]->uploadedByContributor);
        static::assertSame('Supplier 1', $productFiles[0]->uploadedBySupplier);
        static::assertSame($file1Date->format('Y-m-d H:i:s'), $productFiles[0]->uploadedAt);
    }

    private function createProductFile(string $filename, \DateTimeImmutable $uploadedAt): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_supplier_product_file` (identifier, original_filename, path, uploaded_by_contributor, uploaded_by_supplier, uploaded_at)
            VALUES (:identifier, :original_filename, :path, :contributorEmail, :supplierIdentifier, :uploadedAt)
        SQL;

        $this->get(Connection::class)->executeQuery(
            $sql,
            [
                'identifier' => Uuid::uuid4()->toString(),
                'original_filename' => $filename,
                'path' => sprintf('path/to/%s', $filename),
                'contributorEmail' => 'contributor@megasupplier.com',
                'supplierIdentifier' => '44ce8069-8da1-4986-872f-311737f46f00',
                'uploadedAt' => $uploadedAt->format('Y-m-d H:i:s'),
            ],
        );
    }
}
