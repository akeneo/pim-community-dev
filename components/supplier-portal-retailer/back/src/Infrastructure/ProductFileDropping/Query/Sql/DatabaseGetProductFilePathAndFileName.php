<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePathAndFileName;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFilePathAndFileName;
use Doctrine\DBAL\Connection;

final class DatabaseGetProductFilePathAndFileName implements GetProductFilePathAndFileName
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $productFileIdentifier, string $contributorEmail): ?ProductFilePathAndFileName
    {
        $sql = <<<SQL
            WITH supplier_identifier AS (
                SELECT supplier.identifier
                FROM akeneo_supplier_portal_supplier_contributor contributor
                    INNER JOIN akeneo_supplier_portal_supplier supplier
                        ON contributor.supplier_identifier = supplier.identifier
                WHERE contributor.email = :email
            )
            SELECT path, original_filename
            FROM akeneo_supplier_portal_supplier_file supplier_file
                INNER JOIN akeneo_supplier_portal_supplier supplier
                    ON supplier_file.uploaded_by_supplier = supplier.identifier
                INNER JOIN akeneo_supplier_portal_supplier_contributor contributor
                    ON supplier_file.uploaded_by_contributor = contributor.email
            WHERE supplier_file.identifier = :productFileIdentifier
            AND supplier_file.uploaded_by_supplier IN (
                SELECT identifier
                FROM supplier_identifier
            )
        SQL;

        $productFile = $this->connection->executeQuery(
            $sql,
            [
                'productFileIdentifier' => $productFileIdentifier,
                'email' => $contributorEmail,
            ],
        )->fetchAssociative();

        if (false === $productFile) {
            return null;
        }

        return new ProductFilePathAndFileName($productFile['original_filename'], $productFile['path']);
    }
}
