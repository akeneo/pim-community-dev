<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetAllSupplierFiles;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

final class DatabaseGetAllSupplierFilesIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturnsAnEmptyArrayIfThereIsNoFile(): void
    {
        static::assertCount(0, ($this->get(GetAllSupplierFiles::class))());
    }

    /** @test */
    public function itGetsNoMoreThanTwentyFiveProductFilesAtATime(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');

        for ($i = 1; 30 >= $i; $i++) {
            $this->createProductFile('file.xlsx', new \DateTimeImmutable());
        }

        static::assertCount(25, $this->get(GetAllSupplierFiles::class)());
    }

    /** @test */
    public function itPaginatesTheProductFilesList(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');

        for ($i = 1; 30 >= $i; $i++) {
            $this->createProductFile('file.xlsx', new \DateTimeImmutable());
        }

        $supplierFiles = $this->get(GetAllSupplierFiles::class)(2);

        static::assertCount(5, $supplierFiles);
    }

    /** @test */
    public function itSortsTheProductFilesListByUploadedDateDescending(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');

        $this->createProductFile('file1.xlsx', (new \DateTimeImmutable())->modify('-10 DAY'));
        $this->createProductFile('file2.xlsx', new \DateTimeImmutable());
        $this->createProductFile('file3.xlsx', (new \DateTimeImmutable())->modify('-2 DAY'));

        $supplierFiles = $this->get(GetAllSupplierFiles::class)();

        static::assertSame('file2.xlsx', $supplierFiles[0]->originalFilename);
        static::assertSame('file3.xlsx', $supplierFiles[1]->originalFilename);
        static::assertSame('file1.xlsx', $supplierFiles[2]->originalFilename);
    }

    /** @test */
    public function itReturnsAnArrayOfReadModels(): void
    {
        $file1Date = new \DateTimeImmutable();
        $file2Date = (new \DateTimeImmutable())->modify('-2 DAY');

        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');
        $this->createProductFile('file1.xlsx', $file1Date);
        $this->createProductFile('file2.xlsx', $file2Date);

        $supplierFiles = $this->get(GetAllSupplierFiles::class)();

        static::assertSame('file1.xlsx', $supplierFiles[0]->originalFilename);
        static::assertSame('contributor@megasupplier.com', $supplierFiles[0]->uploadedByContributor);
        static::assertSame('Supplier 1', $supplierFiles[0]->uploadedBySupplier);
        static::assertSame($file1Date->format('Y-m-d H:i:s'), $supplierFiles[0]->uploadedAt);
    }

    private function createSupplier(string $identifier, string $code, string $label): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_supplier` (identifier, code, label)
            VALUES (:identifier, :code, :label)
        SQL;

        $this->get(Connection::class)->executeQuery(
            $sql,
            [
                'identifier' => $identifier,
                'code' => $code,
                'label' => $label,
            ],
        );
    }

    private function createProductFile(string $filename, \DateTimeImmutable $uploadedAt): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_supplier_file` (identifier, original_filename, path, uploaded_by_contributor, uploaded_by_supplier, uploaded_at)
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
