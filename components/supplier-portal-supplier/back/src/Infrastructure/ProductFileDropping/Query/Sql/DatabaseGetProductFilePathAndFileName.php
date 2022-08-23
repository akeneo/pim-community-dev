<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\GetProductFilePathAndFileName;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Read\Model\ProductFilePathAndFileName;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Doctrine\DBAL\Connection;

final class DatabaseGetProductFilePathAndFileName implements GetProductFilePathAndFileName
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(Identifier $productFileIdentifier, string $contributorEmail): ?ProductFilePathAndFileName
    {
        $sql = <<<SQL
            WITH supplier_identifier AS (
                SELECT supplier.identifier
                FROM akeneo_supplier_portal_contributor_account contributor_account
                    INNER JOIN akeneo_supplier_portal_supplier_contributor contributor
                        ON contributor_account.email = contributor.email
                    INNER JOIN akeneo_supplier_portal_supplier supplier
                        ON contributor.supplier_identifier = supplier.identifier
                WHERE contributor_account.email = :email
            ),
            contributor_identifiers_of_the_same_supplier AS (
                SELECT contributor_account.id
                FROM akeneo_supplier_portal_contributor_account contributor_account
                INNER JOIN akeneo_supplier_portal_supplier_contributor contributor
                    ON contributor_account.email = contributor.email
                WHERE contributor.supplier_identifier IN (
                    SELECT identifier
                    FROM supplier_identifier
                )
            )
            SELECT path, original_filename
            FROM akeneo_supplier_portal_supplier_file supplier_file
                INNER JOIN akeneo_supplier_portal_supplier supplier
                    ON supplier_file.uploaded_by_supplier = supplier.identifier
                INNER JOIN akeneo_supplier_portal_supplier_contributor contributor
                    ON supplier_file.uploaded_by_contributor = contributor.email
                INNER JOIN akeneo_supplier_portal_contributor_account contributor_account
                    ON contributor.email = contributor_account.email
            WHERE supplier_file.identifier = :productFileIdentifier
            AND contributor_account.id IN (
                SELECT id
                FROM contributor_identifiers_of_the_same_supplier
            )
        SQL;

        $productFile = $this->connection->executeQuery(
            $sql,
            [
                'productFileIdentifier' => (string) $productFileIdentifier,
                'email' => $contributorEmail,
            ],
        )->fetchAssociative();

        if (false === $productFile) {
            return null;
        }

        return new ProductFilePathAndFileName($productFile['original_filename'], $productFile['path']);
    }
}
