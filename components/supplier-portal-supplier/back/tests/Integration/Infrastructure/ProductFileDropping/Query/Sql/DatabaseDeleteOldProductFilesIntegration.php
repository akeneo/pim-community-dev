<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\DeleteOldProductFiles;
use Akeneo\SupplierPortal\Supplier\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

final class DatabaseDeleteOldProductFilesIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itDeletesOldProductFiles(): void
    {
        $this->createSupplier();
        $this->createSupplierProductFiles();

        $this->get(DeleteOldProductFiles::class)();
        $supplierProductFilenames = $this->findSupplierProductFiles();

        static::assertEqualsCanonicalizing([
            ['original_filename' => 'file1.xlsx'],
            ['original_filename' => 'file2.xlsx'],
        ], $supplierProductFilenames);
    }

    private function createSupplier(): void
    {
        $sql = <<<SQL
            INSERT INTO akeneo_supplier_portal_supplier (identifier, code, label) 
            VALUES ('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7', 'supplier-1', 'Supplier 1');
        SQL;

        $this->get(Connection::class)->executeStatement($sql);
    }

    private function createSupplierProductFiles(): void
    {
        $connection = $this->get(Connection::class);

        for ($i = 0; 3 > $i; $i++) {
            $sql = <<<SQL
                INSERT INTO akeneo_supplier_portal_supplier_file (
                    identifier,
                    original_filename,
                    path,
                    uploaded_by_contributor,
                    uploaded_by_supplier,
                    uploaded_at
                )
                VALUES (
                    :identifier,
                    :filename,
                    :filepath,
                    'contributor@example.com',
                    'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
                    :uploadedAt
                )
            SQL;

            $connection->executeStatement(
                $sql,
                [
                    'identifier' => Uuid::uuid4()->toString(),
                    'filename' => sprintf('file%d.xlsx', $i + 1),
                    'filepath' => sprintf('supplier-1/file%d.xlsx', $i + 1),
                    'uploadedAt' => (new \DateTimeImmutable())->add(
                        \DateInterval::createFromDateString(sprintf('-%d days', ($i + 1) * 40)),
                    )->format('Y-m-d H:i:s'),
                ],
            );
        }
    }

    private function findSupplierProductFiles(): array
    {
        $sql = <<<SQL
            SELECT original_filename
            FROM `akeneo_supplier_portal_supplier_file`
        SQL;

        return $this->get(Connection::class)
            ->executeQuery($sql)
            ->fetchAllAssociative();
    }
}
