<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\Sql;

use Akeneo\OnboarderSerenity\Domain\Supplier\Read\GetSupplier;
use Akeneo\OnboarderSerenity\Domain\Supplier\Read\Model\SupplierWithContributors;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Identifier;
use Doctrine\DBAL\Connection;

final class DatabaseGetSupplier implements GetSupplier
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(Identifier $identifier): ?SupplierWithContributors
    {
        $supplier = $this->connection->executeQuery(
            <<<SQL
                WITH contributor AS (
                    SELECT contributor.supplier_identifier, JSON_ARRAYAGG(email) as contributors
                    FROM `akeneo_onboarder_serenity_supplier_contributor` contributor
                    GROUP BY contributor.supplier_identifier
                )
                SELECT identifier, code, label, contributor.contributors
                FROM `akeneo_onboarder_serenity_supplier` supplier
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
