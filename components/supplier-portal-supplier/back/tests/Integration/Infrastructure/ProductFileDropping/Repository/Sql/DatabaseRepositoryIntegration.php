<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Integration\Infrastructure\ProductFileDropping\Repository\Sql;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Model\SupplierFile;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\SupplierFileRepository;
use Akeneo\SupplierPortal\Supplier\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

final class DatabaseRepositoryIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itSavesASupplierFile(): void
    {
        $this->createSupplier();
        $repository = $this->get(SupplierFileRepository::class);
        $supplierFile = SupplierFile::create(
            'product-file.xlsx',
            '1/2/3/4/product-file.xlsx',
            'contributor@example.com',
            'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7'
        );
        $repository->save($supplierFile);

        $savedSupplierFile = $this->findSupplierFile('product-file.xlsx');

        $this->assertSame($supplierFile->filename(), $savedSupplierFile['filename']);
        $this->assertSame($supplierFile->path(), $savedSupplierFile['path']);
        $this->assertSame($supplierFile->uploadedByContributor(), $savedSupplierFile['uploaded_by_contributor']);
        $this->assertSame($supplierFile->uploadedBySupplier(), $savedSupplierFile['uploaded_by_supplier']);
        $this->assertFalse((bool) $savedSupplierFile['downloaded']);
    }

    private function findSupplierFile(string $filename): ?array
    {
        $sql = <<<SQL
            SELECT *
            FROM `akeneo_supplier_portal_supplier_file`
            WHERE filename = :filename
        SQL;

        $supplierFile = $this->get(Connection::class)
            ->executeQuery($sql, ['filename' => $filename])
            ->fetchAssociative()
        ;

        return $supplierFile ?: null;
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
