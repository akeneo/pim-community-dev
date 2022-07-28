<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\GetSupplierFiles;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

final class DatabaseGetSupplierFilesIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturnsAnEmptyArrayIfThereIsNoFile(): void
    {
        static::assertCount(0, ($this->get(GetSupplierFiles::class))('44ce8069-8da1-4986-872f-311737f46f00'));
    }

    /** @test */
    public function itGetsOnlyTheProductFilesOfAGivenSupplier(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');
        $this->createSupplier('a20576cd-840f-4124-9900-14d581491387', 'supplier_2', 'Supplier 2');

        for ($i = 1; 15 >= $i; $i++) {
            $this->createSupplierFile('path/to/file/file.xlsx', new \DateTimeImmutable(), '44ce8069-8da1-4986-872f-311737f46f00');
        }
        for ($i = 1; 10 >= $i; $i++) {
            $this->createSupplierFile('path/to/file/file.xlsx', new \DateTimeImmutable(), 'a20576cd-840f-4124-9900-14d581491387');
        }

        static::assertCount(15, $this->get(GetSupplierFiles::class)('44ce8069-8da1-4986-872f-311737f46f00'));
    }

    /** @test */
    public function itGetsNoMoreThanTwentyFiveProductFilesAtATime(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');

        for ($i = 1; 30 >= $i; $i++) {
            $this->createSupplierFile('path/to/file/file.xlsx', new \DateTimeImmutable(), '44ce8069-8da1-4986-872f-311737f46f00');
        }

        static::assertCount(25, $this->get(GetSupplierFiles::class)('44ce8069-8da1-4986-872f-311737f46f00'));
    }

    /** @test */
    public function itPaginatesTheProductFilesList(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');

        for ($i = 1; 30 >= $i; $i++) {
            $this->createSupplierFile('path/to/file/file.xlsx', new \DateTimeImmutable(), '44ce8069-8da1-4986-872f-311737f46f00');
        }

        $supplierFiles = $this->get(GetSupplierFiles::class)('44ce8069-8da1-4986-872f-311737f46f00', 2);

        static::assertCount(5, $supplierFiles);
    }

    /** @test */
    public function itSortsTheSupplierFilesListByUploadedDateDescending(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');

        $this->createSupplierFile('path/to/file/file1.xlsx', (new \DateTimeImmutable())->modify('-10 DAY'), '44ce8069-8da1-4986-872f-311737f46f00');
        $this->createSupplierFile('path/to/file/file2.xlsx', new \DateTimeImmutable(), '44ce8069-8da1-4986-872f-311737f46f00');
        $this->createSupplierFile('path/to/file/file3.xlsx', (new \DateTimeImmutable())->modify('-2 DAY'), '44ce8069-8da1-4986-872f-311737f46f00');

        $supplierFiles = $this->get(GetSupplierFiles::class)('44ce8069-8da1-4986-872f-311737f46f00');

        static::assertSame('path/to/file/file2.xlsx', $supplierFiles[0]->path);
        static::assertSame('path/to/file/file3.xlsx', $supplierFiles[1]->path);
        static::assertSame('path/to/file/file1.xlsx', $supplierFiles[2]->path);
    }

    /** @test */
    public function itReturnsAnArrayOfReadModels(): void
    {
        $file1Date = new \DateTimeImmutable();
        $file2Date = (new \DateTimeImmutable())->modify('-2 DAY');

        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');
        $this->createSupplierFile('path/to/file/file1.xlsx', $file1Date, '44ce8069-8da1-4986-872f-311737f46f00');
        $this->createSupplierFile('path/to/file/file2.xlsx', $file2Date, '44ce8069-8da1-4986-872f-311737f46f00', true);

        $supplierFiles = $this->get(GetSupplierFiles::class)('44ce8069-8da1-4986-872f-311737f46f00');

        static::assertSame('path/to/file/file1.xlsx', $supplierFiles[0]->path);
        static::assertSame(false, $supplierFiles[0]->downloaded);
        static::assertSame('contributor@megasupplier.com', $supplierFiles[0]->uploadedByContributor);
        static::assertSame($file1Date->format('Y-m-d H:i:s'), $supplierFiles[0]->uploadedAt);

        static::assertSame(true, $supplierFiles[1]->downloaded);
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

    private function createSupplierFile(string $path, \DateTimeImmutable $uploadedAt, string $supplierIdentifier, bool $downloaded = false): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_supplier_file` (identifier, original_filename, path, uploaded_by_contributor, uploaded_by_supplier, uploaded_at, downloaded)
            VALUES (:identifier, :original_filename, :path, :contributorEmail, :supplierIdentifier, :uploadedAt, :downloaded)
        SQL;

        $this->get(Connection::class)->executeQuery(
            $sql,
            [
                'identifier' => Uuid::uuid4()->toString(),
                'original_filename' => 'file.xlsx',
                'path' => $path,
                'contributorEmail' => 'contributor@megasupplier.com',
                'supplierIdentifier' => $supplierIdentifier,
                'uploadedAt' => $uploadedAt->format('Y-m-d H:i:s'),
                'downloaded' => (int) $downloaded,
            ],
        );
    }
}
