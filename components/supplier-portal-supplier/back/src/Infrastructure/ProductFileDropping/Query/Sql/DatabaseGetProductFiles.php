<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Query\Sql;

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
    public function __invoke(string $contributorEmail): array
    {
        $sql = <<<SQL
            WITH supplier_identifier AS (
                SELECT s.identifier
                FROM akeneo_supplier_portal_supplier s
                INNER JOIN akeneo_supplier_portal_supplier_contributor sc
                    ON s.identifier = sc.supplier_identifier
                WHERE sc.email = :contributorEmail
            )
            SELECT sf.identifier,
                   sf.original_filename,
                   sf.path,
                   sf.uploaded_by_contributor,
                   sf.uploaded_by_supplier,
                   sf.uploaded_at,
                   sf.downloaded
            FROM akeneo_supplier_portal_supplier_file sf
            INNER JOIN supplier_identifier ON supplier_identifier.identifier = uploaded_by_supplier
            ORDER BY uploaded_at DESC
            LIMIT 25;
        SQL;

        $supplierFileRows = $this->connection->executeQuery(
            $sql,
            ['contributorEmail' => $contributorEmail],
        )->fetchAllAssociative();

        $supplierFiles = [];
        foreach ($supplierFileRows as $supplierFileRow) {
            $supplierFiles[] = new SupplierFile(
                $supplierFileRow['identifier'],
                $supplierFileRow['original_filename'],
                $supplierFileRow['path'],
                $supplierFileRow['uploaded_by_contributor'],
                $supplierFileRow['uploaded_at'],
            );
        }

        return $supplierFiles;
    }
}
