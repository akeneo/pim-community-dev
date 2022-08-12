<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetCodeFromSupplierFileIdentifier;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseGetCodeFromSupplierFileIdentifierIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itGetsCodeFromIdentifier(): void
    {
        $this->createSupplier();
        $this->createSupplierFile();

        $supplierCode = ($this->get(GetCodeFromSupplierFileIdentifier::class))('a3aac0e2-9eb9-4203-8af2-5425b2062ad4');

        static::assertSame('supplier_code', $supplierCode);
    }

    /** @test */
    public function itReturnsNullIfThereIsNoSupplierForTheGivenSupplierFileIdentifier(): void
    {
        $this->createSupplier();

        $supplierCode = ($this->get(GetCodeFromSupplierFileIdentifier::class))('606abe11-353f-470c-aa1c-7f9e793b29a0');

        static::assertNull($supplierCode);
    }

    private function createSupplier(): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_supplier` (identifier, code, label)
            VALUES (:identifier, :code, :label)
        SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'identifier' => 'a3aac0e2-9eb9-4203-8af2-5425b2062ad4',
                'code' => 'supplier_code',
                'label' => 'Supplier label',
            ],
        );
    }

    private function createSupplierFile(): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_supplier_file` (
                identifier, 
                original_filename, 
                path, 
                uploaded_by_contributor, 
                uploaded_by_supplier, 
                uploaded_at, 
                downloaded
            ) VALUES (
                :identifier, 
                :original_filename, 
                :path, 
                :uploaded_by_contributor, 
                :uploaded_by_supplier, 
                :uploaded_at, 
                :downloaded
            )
        SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'identifier' => 'a3aac0e2-9eb9-4203-8af2-5425b2062ad4',
                'original_filename' => 'products.xlsx',
                'path' => 'path/to/products.xlsx',
                'uploaded_by_contributor' => 'contributor@example.com',
                'uploaded_by_supplier' => 'a3aac0e2-9eb9-4203-8af2-5425b2062ad4',
                'uploaded_at' => '2022-08-11 15:40:48',
                'downloaded' => 0,
            ],
        );
    }
}
