<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\GetAllSupplierCodes;
use Doctrine\DBAL\Connection;

final class DatabaseGetAllSupplierCodes implements GetAllSupplierCodes
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(): array
    {
        $sql = <<<SQL
            SELECT code
            FROM `akeneo_supplier_portal_supplier`
        SQL;

        return array_map(
            fn (array $result) => $result['code'],
            $this->connection->executeQuery($sql)->fetchAllAssociative(),
        );
    }
}
