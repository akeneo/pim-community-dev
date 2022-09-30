<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePathsOfOldProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

final class DatabaseGetProductFilePathsOfOldProductFilesIntegration extends SqlIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withIdentifier('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7')
                ->withCode('supplier_1')
                ->build(),
        );
    }

    /** @test */
    public function itGetsNothingIfThereIsNoOldProductFiles(): void
    {
        $this->createNonOldSupplierProductFiles();

        static::assertEmpty($this->get(GetProductFilePathsOfOldProductFiles::class)());
    }

    /** @test */
    public function itGetsTheProductFilePathsOfOldProductFiles(): void
    {
        $this->createSupplierProductFiles();

        $sut = $this->get(GetProductFilePathsOfOldProductFiles::class);
        $productFilePaths = ($sut)();

        static::assertEqualsCanonicalizing(['supplier-1/file3.xlsx', 'supplier-1/file4.xlsx'], $productFilePaths);
    }

    private function createSupplierProductFiles(): void
    {
        $connection = $this->get(Connection::class);

        for ($i = 0; 4 > $i; $i++) {
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

    private function createNonOldSupplierProductFiles(): void
    {
        $connection = $this->get(Connection::class);

        for ($i = 0; 2 > $i; $i++) {
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
}
