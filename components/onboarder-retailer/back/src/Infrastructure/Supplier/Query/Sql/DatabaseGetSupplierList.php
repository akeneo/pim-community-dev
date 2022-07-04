<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierList;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\SupplierWithContributorCount;
use Doctrine\DBAL\Connection;

final class DatabaseGetSupplierList implements GetSupplierList
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(int $page = 1, string $search = ''): array
    {
        $page = max($page, 1);
        $sql = <<<SQL
            WITH contributor AS (
                SELECT contributor.supplier_identifier, COUNT(id) as contributors_count
                FROM akeneo_supplier_portal_supplier_contributor contributor
                GROUP BY contributor.supplier_identifier
            )
            SELECT identifier, code, label, IFNULL(contributors_count, 0) as contributors_count
            FROM `akeneo_supplier_portal_supplier` as supplier
            LEFT JOIN contributor ON contributor.supplier_identifier = supplier.identifier
            WHERE label LIKE :search
            ORDER BY label
            LIMIT :limit
            OFFSET :offset
        SQL;

        return array_map(fn (array $supplier) => new SupplierWithContributorCount(
            $supplier['identifier'],
            $supplier['code'],
            $supplier['label'],
            (int) $supplier['contributors_count'],
        ), $this->connection->executeQuery(
            $sql,
            [
                'search' => "%$search%",
                'offset' => self::NUMBER_OF_SUPPLIERS_PER_PAGE * ($page - 1),
                'limit' => self::NUMBER_OF_SUPPLIERS_PER_PAGE,
            ],
            [
                'search' => \PDO::PARAM_STR,
                'offset' => \PDO::PARAM_INT,
                'limit' => \PDO::PARAM_INT,
            ],
        )->fetchAllAssociative());
    }
}
