<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListProductFilesForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile;
use Doctrine\DBAL\Connection;

final class DatabaseListProductFilesForSupplier implements ListProductFilesForSupplier
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $supplierIdentifier): array
    {
        $sql = <<<SQL
            SELECT supplier_file.identifier, original_filename, uploaded_by_contributor, uploaded_at
            FROM akeneo_supplier_portal_supplier_product_file supplier_file
            where uploaded_by_supplier = :supplierIdentifier
            ORDER BY uploaded_at DESC 
            LIMIT :limit
        SQL;

        return array_map(fn (array $file) => new ProductFile(
            $file['identifier'],
            $file['original_filename'],
            $file['uploaded_by_contributor'],
            $supplierIdentifier,
            $file['uploaded_at'],
        ), $this->connection->executeQuery(
            $sql,
            [
                'supplierIdentifier' => $supplierIdentifier,
                'limit' => ListProductFilesForSupplier::NUMBER_OF_PRODUCT_FILES,
            ],
            [
                'supplierIdentifier' => \PDO::PARAM_STR,
                'offset' => \PDO::PARAM_INT,
                'limit' => \PDO::PARAM_INT,
            ],
        )->fetchAllAssociative());
    }
}
