<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Repository\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

final class DatabaseRepositoryIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itSavesAProductFile(): void
    {
        $this->createSupplier();
        $repository = $this->get(ProductFileRepository::class);
        $productFile = ProductFile::create(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'product-file.xlsx',
            '1/2/3/4/product-file.xlsx',
            'contributor@example.com',
            new Supplier('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7', 'los_pollos_hermanos', 'Los Pollos Hermanos'),
        );
        $repository->save($productFile);

        $savedProductFile = $this->findProductFile('product-file.xlsx');

        $this->assertSame($productFile->originalFilename(), $savedProductFile['original_filename']);
        $this->assertSame($productFile->path(), $savedProductFile['path']);
        $this->assertSame($productFile->contributorEmail(), $savedProductFile['uploaded_by_contributor']);
        $this->assertSame($productFile->supplierIdentifier(), $savedProductFile['uploaded_by_supplier']);
        $this->assertFalse((bool) $savedProductFile['downloaded']);
    }

    private function findProductFile(string $originalFilename): ?array
    {
        $sql = <<<SQL
            SELECT *
            FROM `akeneo_supplier_portal_supplier_file`
            WHERE original_filename = :original_filename
        SQL;

        $productFile = $this->get(Connection::class)
            ->executeQuery($sql, ['original_filename' => $originalFilename])
            ->fetchAssociative();

        return $productFile ?: null;
    }

    private function createSupplier(): void
    {
        $sql = <<<SQL
            INSERT INTO akeneo_supplier_portal_supplier (identifier, code, label) 
            VALUES ('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7', 'supplier-1', 'Supplier 1');
        SQL;

        $this->get(Connection::class)->executeStatement($sql);
    }
}
