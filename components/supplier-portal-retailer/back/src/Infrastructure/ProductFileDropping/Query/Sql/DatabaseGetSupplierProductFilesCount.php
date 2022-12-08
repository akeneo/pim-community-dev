<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetSupplierProductFilesCount;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\ProductFileImportStatus;
use Doctrine\DBAL\Connection;

final class DatabaseGetSupplierProductFilesCount implements GetSupplierProductFilesCount
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(
        string $supplierIdentifier,
        string $search = '',
        ?ProductFileImportStatus $status = null,
    ): int {
        return (int) $this->connection->executeQuery(
            <<<SQL
            SELECT COUNT(*)
            FROM `akeneo_supplier_portal_supplier_product_file` AS product_file
            LEFT JOIN `akeneo_supplier_portal_product_file_imported_by_job_execution` AS product_file_import
                ON product_file_import.product_file_identifier = product_file.identifier
            WHERE product_file.uploaded_by_supplier = :supplierIdentifier
            AND product_file.original_filename LIKE :search
            AND COALESCE(product_file_import.import_status, :toImportStatus) IN (:status)
        SQL,
            [
                'supplierIdentifier' => $supplierIdentifier,
                'search' => "%$search%",
                'toImportStatus' => ProductFileImportStatus::TO_IMPORT->value,
                'status' => null === $status ? array_column(
                    ProductFileImportStatus::cases(),
                    'value',
                ) : [$status->value],
            ],
            [
                'supplierIdentifier' => \PDO::PARAM_STR,
                'search' => \PDO::PARAM_STR,
                'toImportStatus' => \PDO::PARAM_STR,
                'status' => Connection::PARAM_STR_ARRAY,
            ],
        )->fetchOne();
    }
}
