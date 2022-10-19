<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierWithContributors;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\SupplierWithContributors;
use Doctrine\DBAL\Connection;

final class DatabaseGetSupplierWithContributors implements GetSupplierWithContributors
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $identifier): ?SupplierWithContributors
    {
        $supplier = $this->connection->executeQuery(
            <<<SQL
                WITH contributor AS (
                    SELECT contributor.supplier_identifier, JSON_ARRAYAGG(email) as contributors
                    FROM `akeneo_supplier_portal_supplier_contributor` contributor
                    GROUP BY contributor.supplier_identifier
                )
                SELECT identifier, code, label, contributor.contributors
                FROM `akeneo_supplier_portal_supplier` supplier
                LEFT JOIN contributor ON contributor.supplier_identifier = supplier.identifier
                WHERE identifier = :identifier
            SQL
            ,
            [
                'identifier' => $identifier,
            ],
        )->fetchAssociative();

        return false !== $supplier ? new SupplierWithContributors(
            $supplier['identifier'],
            $supplier['code'],
            $supplier['label'],
            null !== $supplier['contributors'] ? json_decode($supplier['contributors']) : [],
        ) : null;
    }
}
