<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Infrastructure\Supplier\Query\Sql;

use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Read\GetSupplierCount;
use Doctrine\DBAL\Connection;

final class DatabaseGetSupplierCount implements GetSupplierCount
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $search = ''): int
    {
        return (int) $this->connection->executeQuery(
            <<<SQL
            SELECT COUNT(*)
            FROM `akeneo_onboarder_serenity_supplier`
            WHERE label LIKE :search
        SQL,
            [
                'search' => "%$search%",
            ],
            [
                'search' => \PDO::PARAM_STR,
            ],
        )->fetchOne();
    }
}
