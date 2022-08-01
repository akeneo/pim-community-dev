<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\GetSupplierFilesCount;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

final class DatabaseGetSupplierFilesCountIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturns0IfThereIsNoFile(): void
    {
        static::assertSame(0, $this->get(GetSupplierFilesCount::class)('44ce8069-8da1-4986-872f-311737f46f00'));
    }

    /** @test */
    public function itReturnsTheTotalNumberOfSupplierFiles(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');
        $this->createSupplier('a20576cd-840f-4124-9900-14d581491387', 'supplier_2', 'Supplier 2');

        for ($i = 1; 15 >= $i; $i++) {
            $this->createSupplierFile('44ce8069-8da1-4986-872f-311737f46f00');
        }
        for ($i = 1; 10 >= $i; $i++) {
            $this->createSupplierFile('a20576cd-840f-4124-9900-14d581491387');
        }

        static::assertSame(15, $this->get(GetSupplierFilesCount::class)('44ce8069-8da1-4986-872f-311737f46f00'));
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

    private function createSupplierFile(string $supplierIdentifier): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_supplier_file` (identifier, original_filename, path, uploaded_by_contributor, uploaded_by_supplier, uploaded_at, downloaded)
            VALUES (:identifier, :originalFilename, :path, :contributorEmail, :supplierIdentifier, :uploadedAt, :downloaded)
        SQL;

        $this->get(Connection::class)->executeQuery(
            $sql,
            [
                'identifier' => Uuid::uuid4()->toString(),
                'originalFilename' => 'file.xlsx',
                'path' => 'path/to/file/file.xlsx',
                'contributorEmail' => 'contributor@megasupplier.com',
                'supplierIdentifier' => $supplierIdentifier,
                'uploadedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                'downloaded' => 0,
            ],
        );
    }
}
