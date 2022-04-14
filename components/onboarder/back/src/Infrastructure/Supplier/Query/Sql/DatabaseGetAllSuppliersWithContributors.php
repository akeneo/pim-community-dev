<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\Sql;

use Akeneo\OnboarderSerenity\Domain\Read\Supplier\GetAllSuppliersWithContributors;
use Akeneo\OnboarderSerenity\Domain\Read\Supplier\Model\SupplierWithContributors;
use Doctrine\DBAL\Connection;

final class DatabaseGetAllSuppliersWithContributors implements GetAllSuppliersWithContributors
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(): array
    {
        $sql = <<<SQL
            WITH contributor AS (
                SELECT contributor.supplier_identifier, JSON_ARRAYAGG(email) as contributors
                FROM `akeneo_onboarder_serenity_supplier_contributor` contributor
                GROUP BY contributor.supplier_identifier
            )
            SELECT identifier, code, label, contributor.contributors
            FROM `akeneo_onboarder_serenity_supplier` supplier
            LEFT JOIN contributor ON contributor.supplier_identifier = supplier.identifier
            ORDER BY code;
        SQL;

        return array_map(
            fn (array $supplier) => new SupplierWithContributors(
                $supplier['identifier'],
                $supplier['code'],
                $supplier['label'],
                null !== $supplier['contributors']
                    ? json_decode($supplier['contributors'], true)
                    : [],
            ),
            $this->connection->executeQuery($sql)->fetchAllAssociative(),
        );
    }
}
