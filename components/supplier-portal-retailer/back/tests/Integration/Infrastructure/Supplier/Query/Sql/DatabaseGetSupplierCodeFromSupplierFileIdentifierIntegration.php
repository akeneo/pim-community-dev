<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierCodeFromProductFileIdentifier;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseGetSupplierCodeFromSupplierFileIdentifierIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itGetsSupplierCodeFromSupplierFileIdentifier(): void
    {
        $this->createSupplier();
        $this->createSupplierFile('ede6024b-bdce-47d0-ba0c-7132f217992f');

        $supplierCode = ($this->get(GetSupplierCodeFromProductFileIdentifier::class))('ede6024b-bdce-47d0-ba0c-7132f217992f');

        static::assertSame('supplier_code', $supplierCode);
    }

    /** @test */
    public function itReturnsNullIfThereIsNoFileForTheGivenSupplierFileIdentifier(): void
    {
        $this->createSupplier();
        $this->createSupplierFile('3f91df5e-986d-43de-99b0-113bfdae7a77');

        $supplierCode = ($this->get(GetSupplierCodeFromProductFileIdentifier::class))('606abe11-353f-470c-aa1c-7f9e793b29a0');

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

    private function createSupplierFile(string $identifier): void
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
                'identifier' => $identifier,
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
