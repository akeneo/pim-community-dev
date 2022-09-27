<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\DeleteOldProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builders\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

final class DatabaseDeleteOldProductFilesIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itDeletesOldProductFiles(): void
    {
        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withIdentifier('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7')
                ->build(),
        );
        $this->createSupplierProductFiles();

        $this->get(DeleteOldProductFiles::class)();
        $supplierProductFilenames = $this->findSupplierProductFiles();

        static::assertEqualsCanonicalizing([
            ['original_filename' => 'file1.xlsx'],
            ['original_filename' => 'file2.xlsx'],
        ], $supplierProductFilenames);
    }

    private function createSupplierProductFiles(): void
    {
        $connection = $this->get(Connection::class);

        for ($i = 0; 3 > $i; $i++) {
            $sql = <<<SQL
                INSERT INTO akeneo_supplier_portal_supplier_product_file (
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
            FROM `akeneo_supplier_portal_supplier_product_file`
        SQL;

        return $this->get(Connection::class)
            ->executeQuery($sql)
            ->fetchAllAssociative();
    }
}
