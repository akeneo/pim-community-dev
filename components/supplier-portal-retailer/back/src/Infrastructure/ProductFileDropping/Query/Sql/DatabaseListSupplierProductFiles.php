<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListSupplierProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile;
use Doctrine\DBAL\Connection;

final class DatabaseListSupplierProductFiles implements ListSupplierProductFiles
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $supplierIdentifier, int $page = 1): array
    {
        $page = max($page, 1);

        $sql = <<<SQL
            SELECT product_file.identifier, original_filename, uploaded_by_contributor, uploaded_at
            FROM akeneo_supplier_portal_supplier_product_file product_file
            where uploaded_by_supplier = :supplierIdentifier
            ORDER BY uploaded_at DESC 
            LIMIT :limit
            OFFSET :offset
        SQL;

        return array_map(fn (array $file) => new ProductFile(
            $file['identifier'],
            $file['original_filename'],
            null,
            $file['uploaded_by_contributor'],
            $supplierIdentifier,
            $file['uploaded_at'],
        ), $this->connection->executeQuery(
            $sql,
            [
                'supplierIdentifier' => $supplierIdentifier,
                'offset' => ListSupplierProductFiles::NUMBER_OF_PRODUCT_FILES_PER_PAGE * ($page - 1),
                'limit' => ListSupplierProductFiles::NUMBER_OF_PRODUCT_FILES_PER_PAGE,
            ],
            [
                'supplierIdentifier' => \PDO::PARAM_STR,
                'offset' => \PDO::PARAM_INT,
                'limit' => \PDO::PARAM_INT,
            ],
        )->fetchAllAssociative());
    }
}
