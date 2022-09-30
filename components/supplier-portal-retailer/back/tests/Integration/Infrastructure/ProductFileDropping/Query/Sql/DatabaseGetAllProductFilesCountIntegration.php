<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetAllProductFilesCount;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

final class DatabaseGetAllProductFilesCountIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturns0IfThereIsNoFile(): void
    {
        static::assertSame(0, $this->get(GetAllProductFilesCount::class)());
    }

    /** @test */
    public function itReturnsTheTotalNumberOfProductFiles(): void
    {
        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f00')
                ->withCode('supplier_1')
                ->withLabel('Supplier 1')
                ->build(),
        );

        for ($i = 1; 15 >= $i; $i++) {
            $this->createProductFile('path/to/file/file.xlsx', new \DateTimeImmutable());
        }

        static::assertSame(15, $this->get(GetAllProductFilesCount::class)());
    }

    private function createProductFile(string $path, \DateTimeImmutable $uploadedAt, bool $downloaded = false): void
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
                'path' => $path,
                'contributorEmail' => 'contributor@megasupplier.com',
                'supplierIdentifier' => '44ce8069-8da1-4986-872f-311737f46f00',
                'uploadedAt' => $uploadedAt->format('Y-m-d H:i:s'),
                'downloaded' => (int) $downloaded,
            ],
        );
    }
}
