<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\Sql;

use Akeneo\OnboarderSerenity\Domain\Supplier\Read\SupplierContributorsBelongingToAnotherSupplier;
use Doctrine\DBAL\Connection;

final class DatabaseSupplierContributorsBelongingToAnotherSupplier implements SupplierContributorsBelongingToAnotherSupplier
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $supplierIdentifier, array $emails): array
    {
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
                'emails' => Connection::PARAM_STR_ARRAY,
            ],
        );

        return array_map(
            fn (array $supplierContributor) => $supplierContributor['email'],
            $statement->fetchAllAssociative(),
        );
    }
}
