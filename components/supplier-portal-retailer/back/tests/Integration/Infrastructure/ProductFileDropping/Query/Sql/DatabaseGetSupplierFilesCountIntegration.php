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
        static::assertSame(0, $this->get(GetSupplierFilesCount::class)());
    }

    /** @test */
    public function itReturnsTheTotalNumberOfSupplierFiles(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');

        for ($i = 1; 15 >= $i; $i++) {
            $this->createSupplierFile('path/to/file/file.xlsx', new \DateTimeImmutable());
        }

        static::assertSame(15, $this->get(GetSupplierFilesCount::class)());
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
                'supplierIdentifier' => '44ce8069-8da1-4986-872f-311737f46f00',
                'uploadedAt' => $uploadedAt->format('Y-m-d H:i:s'),
                'downloaded' => (int) $downloaded,
            ],
        );
    }
}
