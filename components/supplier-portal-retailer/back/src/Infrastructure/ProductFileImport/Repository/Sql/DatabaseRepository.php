<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileImport\Repository\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImport;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\ProductFileImportRepository;
use Doctrine\DBAL\Connection;

final class DatabaseRepository implements ProductFileImportRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function save(ProductFileImport $productFileImport): void
    {
        $sql = <<<SQL
            REPLACE INTO akeneo_supplier_portal_product_file_imported_by_job_execution (product_file_identifier, job_execution_id, import_status, finished_at)
            VALUES (:productFileIdentifier, :jobExecutionId, :jobExecutionResult, :finishedAt)
SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'productFileIdentifier' => $productFileImport->productFileIdentifier(),
                'jobExecutionId' => $productFileImport->importExecutionId(),
                'jobExecutionResult' => $productFileImport->fileImportStatus()->value,
                'finishedAt' => null,
            ],
        );
    }
}
