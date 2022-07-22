<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\GetProductFiles;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Read\Model\SupplierFile;
use Doctrine\DBAL\Connection;

final class DatabaseGetProductFiles implements GetProductFiles
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @return SupplierFile[]
     */
    public function __invoke(Identifier $supplierIdentifier): array
    {
        $sql = <<<SQL
            WITH contributorEmail AS (
                SELECT email
                FROM akeneo_supplier_portal_supplier_contributor sc
                INNER JOIN akeneo_supplier_portal_supplier s on sc.supplier_identifier = s.identifier
                WHERE s.identifier = :supplierIdentifier
            )
            SELECT identifier,
                   filename,
                   path,
                   uploaded_by_contributor,
                   uploaded_by_supplier,
                   uploaded_at,
                   downloaded
            FROM akeneo_supplier_portal_supplier_file
            LEFT JOIN contributorEmail ON uploaded_by_contributor = email
            WHERE uploaded_by_supplier = :supplierIdentifier
            ORDER BY uploaded_at DESC
            LIMIT 25;
        SQL;

        $supplierFileRows = $this->connection->executeQuery(
            $sql,
            ['supplierIdentifier' => $supplierIdentifier],
        )->fetchAllAssociative();

        $supplierFiles = [];
        foreach ($supplierFileRows as $supplierFileRow) {
            $supplierFiles[] = new SupplierFile(
                $supplierFileRow['identifier'],
                $supplierFileRow['filename'],
                $supplierFileRow['path'],
                $supplierFileRow['uploaded_by_contributor'],
                $supplierFileRow['uploaded_at'],
            );
        }

        return $supplierFiles;
    }
}
