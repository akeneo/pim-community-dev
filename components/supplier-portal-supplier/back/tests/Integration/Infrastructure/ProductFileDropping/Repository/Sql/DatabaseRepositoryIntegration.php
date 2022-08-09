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
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'product-file.xlsx',
            '1/2/3/4/product-file.xlsx',
            'contributor@example.com',
            'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
        );
        $repository->save($supplierFile);

        $savedSupplierFile = $this->findSupplierFile('product-file.xlsx');

        $this->assertSame($supplierFile->originalFilename(), $savedSupplierFile['original_filename']);
        $this->assertSame($supplierFile->path(), $savedSupplierFile['path']);
        $this->assertSame($supplierFile->uploadedByContributor(), $savedSupplierFile['uploaded_by_contributor']);
        $this->assertSame($supplierFile->uploadedBySupplier(), $savedSupplierFile['uploaded_by_supplier']);
        $this->assertFalse((bool) $savedSupplierFile['downloaded']);
    }

    private function findSupplierFile(string $originalFilename): ?array
    {
        $sql = <<<SQL
            SELECT *
            FROM `akeneo_supplier_portal_supplier_file`
            WHERE original_filename = :original_filename
        SQL;

        $supplierFile = $this->get(Connection::class)
            ->executeQuery($sql, ['original_filename' => $originalFilename])
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
