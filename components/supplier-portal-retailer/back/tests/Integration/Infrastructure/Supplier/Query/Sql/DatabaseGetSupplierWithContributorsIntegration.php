<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierWithContributors;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

final class DatabaseGetSupplierWithContributorsIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturnsNullIfThereIsNoSupplier(): void
    {
        static::assertNull(($this->get(GetSupplierWithContributors::class))(
            Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02')
        ));
    }

    /** @test */
    public function itGetsASupplierWithContributors(): void
    {
        $this->createSupplier();
        $this->createContributor('contributor1@example.com');
        $this->createContributor('contributor2@example.com');

        $supplier = ($this->get(GetSupplierWithContributors::class))(
            Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02')
        );

        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', $supplier->identifier);
        static::assertSame('supplier_code', $supplier->code);
        static::assertSame('Supplier code', $supplier->label);
        static::assertEquals(
            [
                'contributor1@example.com',
                'contributor2@example.com',
            ],
            array_values($supplier->contributors),
        );
    }

    /** @test */
    public function itGetsASupplierWithNoContributors(): void
    {
        $this->createSupplier();

        $supplier = ($this->get(GetSupplierWithContributors::class))(
            Identifier::fromString('44ce8069-8da1-4986-872f-311737f46f02')
        );

        static::assertSame('44ce8069-8da1-4986-872f-311737f46f02', $supplier->identifier);
        static::assertSame('supplier_code', $supplier->code);
        static::assertSame('Supplier code', $supplier->label);
        static::assertSame([], $supplier->contributors);
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
