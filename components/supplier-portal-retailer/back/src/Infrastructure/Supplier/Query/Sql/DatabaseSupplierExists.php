<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\SupplierExists;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Code;
use Doctrine\DBAL\Connection;

final class DatabaseSupplierExists implements SupplierExists
{
    public function __construct(private Connection $connection)
    {
    }

    public function fromCode(Code $supplierCode): bool
    {
        $sql = <<<SQL
            SELECT 1
            FROM `akeneo_supplier_portal_supplier`
            WHERE code = :code
        SQL;

        return 1 === $this->connection->executeQuery(
            $sql,
            [
                'code' => (string) $supplierCode,
            ],
        )->rowCount();
    }
}
