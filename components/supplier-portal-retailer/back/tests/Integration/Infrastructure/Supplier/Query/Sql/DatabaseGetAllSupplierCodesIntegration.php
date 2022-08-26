<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\GetAllSupplierCodes;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

final class DatabaseGetAllSupplierCodesIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itGetsNothingIfThereIsNoSupplier(): void
    {
        static::assertEmpty($this->get(GetAllSupplierCodes::class)());
    }

    /** @test */
    public function itGetsAllTheSupplierCodes(): void
    {
        $this->createSupplier('44ce8069-8da1-4986-872f-311737f46f00', 'supplier_1', 'Supplier 1');
        $this->createSupplier('6563b4b1-71c5-420c-bf6c-772108bbbc92', 'supplier_2', 'Supplier 2');

        static::assertEqualsCanonicalizing(['supplier_1', 'supplier_2'], $this->get(GetAllSupplierCodes::class)());
    }

    private function createSupplier(string $identifier, string $code, string $label): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_supplier` (identifier, code, label)
            VALUES (:identifier, :code, :label)
        SQL;

        $this->get(Connection::class)->executeQuery(
            $sql,
            [
                'identifier' => $identifier,
                'code' => $code,
                'label' => $label,
            ],
        );
    }
}
