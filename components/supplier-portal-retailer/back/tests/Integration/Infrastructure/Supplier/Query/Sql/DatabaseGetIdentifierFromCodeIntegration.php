<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetIdentifierFromCode;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseGetIdentifierFromCodeIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itGetsIdentifierFromCode(): void
    {
        $this->createSupplier();

        $supplierIdentifier = ($this->get(GetIdentifierFromCode::class))('supplier_code');

        static::assertSame('a3aac0e2-9eb9-4203-8af2-5425b2062ad4', $supplierIdentifier);
    }

    /** @test */
    public function itReturnsNullIfThereIsNoSupplierForTheGivenCode(): void
    {
        $this->createSupplier();

        $supplierIdentifier = ($this->get(GetIdentifierFromCode::class))('unknown_supplier_code');

        static::assertNull($supplierIdentifier);
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
}
