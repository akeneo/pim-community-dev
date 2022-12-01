<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\CountFilteredProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\ProductFileImportStatus;
use Doctrine\DBAL\Connection;

final class DatabaseCountFilteredProductFiles implements CountFilteredProductFiles
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(string $search = '', ?ProductFileImportStatus $status = null): int
    {
        return (int) $this->connection->executeQuery(
            <<<SQL
            SELECT COUNT(*) FROM 
                (
                    SELECT
                        COALESCE(product_file_import.import_status, :toImportStatus) AS 'product_file_import_status'
                    FROM `akeneo_supplier_portal_supplier_product_file` AS product_file
                    LEFT JOIN `akeneo_supplier_portal_product_file_imported_by_job_execution` AS product_file_import
                        ON product_file_import.product_file_identifier = product_file.identifier
                    WHERE original_filename LIKE :search
                    HAVING product_file_import_status IN (:status)
                ) AS filteredProductCount
        SQL,
            [
                'search' => "%$search%",
                'toImportStatus' => ProductFileImportStatus::TO_IMPORT->value,
                'status' => null === $status ? array_column(
                    ProductFileImportStatus::cases(),
                    'value'
                ) : [$status->value],
            ],
            [
                'search' => \PDO::PARAM_STR,
                'toImportStatus' => \PDO::PARAM_STR,
                'status' => Connection::PARAM_STR_ARRAY,
            ],
        )->fetchOne();
    }
}
