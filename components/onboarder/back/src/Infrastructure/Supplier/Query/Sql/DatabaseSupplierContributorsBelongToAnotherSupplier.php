<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\Sql;

use Akeneo\OnboarderSerenity\Domain\Supplier\Read\SupplierContributorsBelongToAnotherSupplier;
use Doctrine\DBAL\Connection;

final class DatabaseSupplierContributorsBelongToAnotherSupplier implements SupplierContributorsBelongToAnotherSupplier
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(string $supplierIdentifier, array $emails): array
    {
        //An empty array would generate en invalid mysql query, IN operator does not accept an empty array
        if (empty($emails)) {
            return [];
        }

        $query = <<<SQL
SELECT email
FROM akeneo_onboarder_serenity_supplier_contributor
WHERE email IN (:emails)
AND supplier_identifier != :supplierIdentifier
SQL;

        $statement = $this->connection->executeQuery(
            $query,
            [
                'supplierIdentifier' => $supplierIdentifier,
                'emails' => $emails,
            ],
            [
                'supplierIdentifier' => \PDO::PARAM_STR,
                'emails' => Connection::PARAM_STR_ARRAY
            ]
        );

        return array_map(
            fn (array $supplierContributor) => $supplierContributor['email'],
            $statement->fetchAllAssociative()
        );
    }
}
