<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile;
use Doctrine\DBAL\Connection;

final class DatabaseListProductFiles implements ListProductFiles
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(int $page = 1): array
    {
        $page = max($page, 1);

        $sql = <<<SQL
            SELECT product_file.identifier, original_filename, uploaded_by_contributor, supplier.label AS supplier, uploaded_at
            FROM akeneo_supplier_portal_supplier_product_file product_file
            INNER JOIN akeneo_supplier_portal_supplier supplier on product_file.uploaded_by_supplier = supplier.identifier
            ORDER BY uploaded_at DESC 
            LIMIT :limit
            OFFSET :offset
        SQL;

        return array_map(fn (array $file) => new ProductFile(
            $file['identifier'],
            $file['original_filename'],
            null,
            $file['uploaded_by_contributor'],
            $file['supplier'],
            $file['uploaded_at'],
        ), $this->connection->executeQuery(
            $sql,
            [
                'offset' => ListProductFiles::NUMBER_OF_PRODUCT_FILES_PER_PAGE * ($page - 1),
                'limit' => ListProductFiles::NUMBER_OF_PRODUCT_FILES_PER_PAGE,
            ],
            [
                'offset' => \PDO::PARAM_INT,
                'limit' => \PDO::PARAM_INT,
            ],
        )->fetchAllAssociative());
    }
}
