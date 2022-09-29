<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilesCount;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

final class DatabaseGetProductFilesCountIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturns0IfThereIsNoFile(): void
    {
        static::assertSame(0, $this->get(GetProductFilesCount::class)('44ce8069-8da1-4986-872f-311737f46f00'));
    }

    /** @test */
    public function itReturnsTheTotalNumberOfProductFiles(): void
    {
        $supplierRepository = $this->get(Repository::class);
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f00')
                ->withCode('supplier_1')
                ->build(),
        );
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('a20576cd-840f-4124-9900-14d581491387')
                ->withCode('supplier_2')
                ->build(),
        );

        for ($i = 1; 15 >= $i; $i++) {
            $this->createProductFile('44ce8069-8da1-4986-872f-311737f46f00');
        }
        for ($i = 1; 10 >= $i; $i++) {
            $this->createProductFile('a20576cd-840f-4124-9900-14d581491387');
        }

        static::assertSame(15, $this->get(GetProductFilesCount::class)('44ce8069-8da1-4986-872f-311737f46f00'));
    }

    private function createProductFile(string $supplierIdentifier): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_supplier_product_file` (identifier, original_filename, path, uploaded_by_contributor, uploaded_by_supplier, uploaded_at, downloaded)
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
