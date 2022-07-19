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
        static::assertCount(0, ($this->get(GetSupplierFiles::class))());
    }

    /** @test */
    public function itGetsNoMoreThanTwentyFiveProductFilesAtATime(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');

        for ($i = 1; 30 >= $i; $i++) {
            $this->createSupplierFile('path/to/file/file.xlsx', new \DateTimeImmutable());
        }

        static::assertCount(25, $this->get(GetSupplierFiles::class)());
    }

    /** @test */
    public function itPaginatesTheProductFilesList(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');

        for ($i = 1; 30 >= $i; $i++) {
            $this->createSupplierFile('path/to/file/file.xlsx', new \DateTimeImmutable());
        }

        $supplierFiles = $this->get(GetSupplierFiles::class)(2);

        static::assertCount(5, $supplierFiles);
    }

    /** @test */
    public function itSortsTheSupplierFilesListByUploadedDateDescending(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');

        $this->createSupplierFile('path/to/file/file1.xlsx', (new \DateTimeImmutable())->modify('-10 DAY'));
        $this->createSupplierFile('path/to/file/file2.xlsx', new \DateTimeImmutable());
        $this->createSupplierFile('path/to/file/file3.xlsx', (new \DateTimeImmutable())->modify('-2 DAY'));

        $supplierFiles = $this->get(GetSupplierFiles::class)();

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
        $this->createSupplierFile('path/to/file/file1.xlsx', $file1Date);
        $this->createSupplierFile('path/to/file/file2.xlsx', $file2Date, true);

        $supplierFiles = $this->get(GetSupplierFiles::class)();

        static::assertSame('path/to/file/file1.xlsx', $supplierFiles[0]->path);
        static::assertSame(false, $supplierFiles[0]->downloaded);
        static::assertSame('contributor@megasupplier.com', $supplierFiles[0]->uploadedByContributor);
        static::assertSame('Supplier 1', $supplierFiles[0]->uploadedBySupplier);
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

    private function createSupplierFile(string $path, \DateTimeImmutable $uploadedAt, bool $downloaded = false): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_supplier_file` (identifier, filename, path, uploaded_by_contributor, uploaded_by_supplier, uploaded_at, downloaded)
            VALUES (:identifier, :filename, :path, :contributorEmail, :supplierIdentifier, :uploadedAt, :downloaded)
        SQL;

        $this->get(Connection::class)->executeQuery(
            $sql,
            [
                'identifier' => Uuid::uuid4()->toString(),
                'filename' => 'file.xlsx',
                'path' => $path,
                'contributorEmail' => 'contributor@megasupplier.com',
                'supplierIdentifier' => '44ce8069-8da1-4986-872f-311737f46f00',
                'uploadedAt' => $uploadedAt->format('Y-m-d H:i:s'),
                'downloaded' => (int) $downloaded,
            ],
        );
    }
}
