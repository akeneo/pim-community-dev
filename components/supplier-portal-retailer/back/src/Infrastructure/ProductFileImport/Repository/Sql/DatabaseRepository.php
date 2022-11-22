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
        if ($this->importJobExist($productFileImport)) {
            $this->updateProductFileImport($productFileImport);
        } else {
            $this->createProductFileImport($productFileImport);
        }
    }

    public function findByImportExecutionId(int $importExecutionId): ?ProductFileImport
    {
        $sql = <<<SQL
            SELECT 
                product_file_identifier,
                job_execution_id,
                import_status,
                finished_at
            FROM akeneo_supplier_portal_product_file_imported_by_job_execution
            WHERE job_execution_id = :jobExecutionId
SQL;
        $productFileImport = $this->connection->executeQuery(
            $sql,
            ['jobExecutionId' => $importExecutionId],
        )->fetchAssociative();

        if (false === $productFileImport) {
            return null;
        }

        return ProductFileImport::hydrate(
            $productFileImport['product_file_identifier'],
            (int) $productFileImport['job_execution_id'],
            $productFileImport['import_status'],
            null !== $productFileImport['finished_at'] ? new \DateTimeImmutable(
                $productFileImport['finished_at'],
            ) : null,
        );
    }

    private function importJobExist(ProductFileImport $productFileImport): bool
    {
        $sql = <<<SQL
            SELECT 1
            FROM akeneo_supplier_portal_product_file_imported_by_job_execution
            WHERE product_file_identifier = :productFileIdentifier;
SQL;
        return (bool) $this
            ->connection
            ->executeQuery($sql, ['productFileIdentifier' => $productFileImport->productFileIdentifier(),])
            ->fetchOne();
    }

    private function updateProductFileImport(ProductFileImport $productFileImport): void
    {
        $sql = <<<SQL
            UPDATE akeneo_supplier_portal_product_file_imported_by_job_execution 
            SET import_status = :jobExecutionResult, finished_at = :finishedAt, job_execution_id = :jobExecutionId
            WHERE product_file_identifier = :productFileIdentifier
SQL;
        $this->connection->executeStatement(
            $sql,
            [
                'productFileIdentifier' => $productFileImport->productFileIdentifier(),
                'jobExecutionId' => $productFileImport->importExecutionId(),
                'jobExecutionResult' => $productFileImport->fileImportStatus(),
                'finishedAt' => $productFileImport->finishedAt(),
            ],
        );
    }

    private function createProductFileImport(ProductFileImport $productFileImport): void
    {
        $sql = <<<SQL
            INSERT INTO akeneo_supplier_portal_product_file_imported_by_job_execution (
                product_file_identifier,
                job_execution_id,
                import_status,
                finished_at
            ) VALUES (
                :productFileIdentifier,
                :jobExecutionId,
                :jobExecutionResult,
                :finishedAt
            )
SQL;
        $this->connection->executeStatement(
            $sql,
            [
                'productFileIdentifier' => $productFileImport->productFileIdentifier(),
                'jobExecutionId' => $productFileImport->importExecutionId(),
                'jobExecutionResult' => $productFileImport->fileImportStatus(),
                'finishedAt' => $productFileImport->finishedAt(),
            ],
        );
    }
}
