<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListProductFiles;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

final class DatabaseListProductFilesIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturnsAnEmptyArrayIfThereIsNoFile(): void
    {
        static::assertCount(0, ($this->get(ListProductFiles::class))('44ce8069-8da1-4986-872f-311737f46f00'));
    }

    /** @test */
    public function itGetsOnlyTheProductFilesOfAGivenSupplier(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');
        $this->createSupplier('a20576cd-840f-4124-9900-14d581491387', 'supplier_2', 'Supplier 2');

        for ($i = 1; 15 >= $i; $i++) {
            $this->createProductFile('file.xlsx', new \DateTimeImmutable(), '44ce8069-8da1-4986-872f-311737f46f00');
        }
        for ($i = 1; 10 >= $i; $i++) {
            $this->createProductFile('file.xlsx', new \DateTimeImmutable(), 'a20576cd-840f-4124-9900-14d581491387');
        }

        static::assertCount(15, $this->get(ListProductFiles::class)('44ce8069-8da1-4986-872f-311737f46f00'));
    }

    /** @test */
    public function itGetsNoMoreThanTwentyFiveProductFilesAtATime(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');

        for ($i = 1; 30 >= $i; $i++) {
            $this->createProductFile('file.xlsx', new \DateTimeImmutable(), '44ce8069-8da1-4986-872f-311737f46f00');
        }

        static::assertCount(25, $this->get(ListProductFiles::class)('44ce8069-8da1-4986-872f-311737f46f00'));
    }

    /** @test */
    public function itPaginatesTheProductFilesList(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');

        for ($i = 1; 30 >= $i; $i++) {
            $this->createProductFile('file.xlsx', new \DateTimeImmutable(), '44ce8069-8da1-4986-872f-311737f46f00');
        }

        $productFiles = $this->get(ListProductFiles::class)('44ce8069-8da1-4986-872f-311737f46f00', 2);

        static::assertCount(5, $productFiles);
    }

    /** @test */
    public function itSortsTheProductFilesListByUploadedDateDescending(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');

        $this->createProductFile('file1.xlsx', (new \DateTimeImmutable())->modify('-10 DAY'), '44ce8069-8da1-4986-872f-311737f46f00');
        $this->createProductFile('file2.xlsx', new \DateTimeImmutable(), '44ce8069-8da1-4986-872f-311737f46f00');
        $this->createProductFile('file3.xlsx', (new \DateTimeImmutable())->modify('-2 DAY'), '44ce8069-8da1-4986-872f-311737f46f00');

        $productFiles = $this->get(ListProductFiles::class)('44ce8069-8da1-4986-872f-311737f46f00');

        static::assertSame('file2.xlsx', $productFiles[0]->originalFilename);
        static::assertSame('file3.xlsx', $productFiles[1]->originalFilename);
        static::assertSame('file1.xlsx', $productFiles[2]->originalFilename);
    }

    /** @test */
    public function itReturnsAnArrayOfReadModels(): void
    {
        $file1Date = new \DateTimeImmutable();
        $file2Date = (new \DateTimeImmutable())->modify('-2 DAY');

        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');
        $this->createProductFile('file1.xlsx', $file1Date, '44ce8069-8da1-4986-872f-311737f46f00');
        $this->createProductFile('file2.xlsx', $file2Date, '44ce8069-8da1-4986-872f-311737f46f00');

        $productFiles = $this->get(ListProductFiles::class)('44ce8069-8da1-4986-872f-311737f46f00');

        static::assertSame('file1.xlsx', $productFiles[0]->originalFilename);
        static::assertSame('contributor@megasupplier.com', $productFiles[0]->uploadedByContributor);
        static::assertSame($file1Date->format('Y-m-d H:i:s'), $productFiles[0]->uploadedAt);
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

    private function createProductFile(string $filename, \DateTimeImmutable $uploadedAt, string $supplierIdentifier): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_supplier_product_file` (identifier, original_filename, path, uploaded_by_contributor, uploaded_by_supplier, uploaded_at)
            VALUES (:identifier, :originalFilename, :path, :contributorEmail, :supplierIdentifier, :uploadedAt)
        SQL;

        $this->get(Connection::class)->executeQuery(
            $sql,
            [
                'identifier' => Uuid::uuid4()->toString(),
                'originalFilename' => $filename,
                'path' => sprintf('path/to/%s', $filename),
                'contributorEmail' => 'contributor@megasupplier.com',
                'supplierIdentifier' => $supplierIdentifier,
                'uploadedAt' => $uploadedAt->format('Y-m-d H:i:s'),
            ],
        );
    }
}
