<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Persistence\Sql;

use Akeneo\OnboarderSerenity\Domain\Read\Supplier\GetSupplierList;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier;
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
            SELECT identifier, code, label
            FROM `akeneo_onboarder_serenity_supplier`
            WHERE label LIKE :search
            ORDER BY label
            LIMIT :limit
            OFFSET :offset
        SQL;

        return array_map(fn(array $supplier) => Supplier\Supplier::create(
            $supplier['identifier'],
            $supplier['code'],
            $supplier['label'],
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
            ]
        )->fetchAllAssociative());
    }
}
