<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierFromContributorEmail;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

final class DatabaseGetSupplierFromContributorEmailIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturnsNullIfSupplierDoesNotHaveContributor(): void
    {
        $this->createSupplier();

        static::assertNull(($this->get(GetSupplierFromContributorEmail::class))(
            'contributor1@example.com'
        ));
    }

    /** @test */
    public function itReturnsNullIfContributorDoesNotExist(): void
    {
        $this->createSupplier();
        $this->createContributor('contributor1@example.com');

        static::assertNull(($this->get(GetSupplierFromContributorEmail::class))(
            'contributor2@example.com'
        ));
    }

    /** @test */
    public function itGetsASupplierFromContributorEmail(): void
    {
        $this->createSupplier();
        $this->createContributor('contributor1@example.com');

        $supplier = ($this->get(GetSupplierFromContributorEmail::class))(
            'contributor1@example.com'
        );

        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', $supplier->identifier);
        static::assertSame('supplier_code', $supplier->code);
        static::assertSame('Supplier code', $supplier->label);
    }

    private function createSupplier(): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_supplier` (identifier, code, label)
            VALUES (:identifier, :code, :label)
        SQL;

        $this->get(Connection::class)->executeQuery(
            $sql,
            [
                'identifier' => '44ce8069-8da1-4986-872f-311737f46f02',
                'code' => 'supplier_code',
                'label' => 'Supplier code',
            ],
        );
    }

    private function createContributor(string $email): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_supplier_contributor` (email, supplier_identifier)
            VALUES (:email, :supplierIdentifier)
        SQL;

        $this->get(Connection::class)->executeQuery(
            $sql,
            [
                'email' => $email,
                'supplierIdentifier' => '44ce8069-8da1-4986-872f-311737f46f02',
            ],
        );
    }
}
