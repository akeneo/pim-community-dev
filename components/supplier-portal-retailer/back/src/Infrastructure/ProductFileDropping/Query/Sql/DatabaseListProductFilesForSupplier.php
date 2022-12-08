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

    public function __invoke(string $supplierIdentifier, int $page = 1, string $search = ''): array
    {
        $page = max($page, 1);

        $sql = <<<SQL
            WITH retailer_comments AS (
                SELECT product_file_identifier, JSON_ARRAYAGG(
                    CASE WHEN content IS NOT NULL THEN JSON_OBJECT(
                            'content', content,
                            'author_email', author_email,
                            'created_at', created_at
                        ) END
                ) AS retailer_comments
                FROM akeneo_supplier_portal_product_file_retailer_comments
                GROUP BY product_file_identifier
            ), supplier_comments AS (
                SELECT product_file_identifier, JSON_ARRAYAGG(
                    CASE WHEN content IS NOT NULL THEN JSON_OBJECT(
                            'content', content,
                            'author_email', author_email,
                            'created_at', created_at
                        ) END
                ) AS supplier_comments
                FROM akeneo_supplier_portal_product_file_supplier_comments
                GROUP BY product_file_identifier
            )
            SELECT
                product_file.identifier,
                original_filename,
                path,
                uploaded_by_contributor,
                uploaded_at,
                rc.retailer_comments,
                sc.supplier_comments,
                comments_read_by_retailer.last_read_at as retailer_last_read_at,
                comments_read_by_supplier.last_read_at as supplier_last_read_at,
                product_file_import.import_status
            FROM akeneo_supplier_portal_supplier_product_file product_file
            LEFT JOIN retailer_comments rc
                ON identifier = rc.product_file_identifier
            LEFT JOIN supplier_comments sc
                ON identifier = sc.product_file_identifier
            LEFT JOIN akeneo_supplier_portal_product_file_comments_read_by_retailer comments_read_by_retailer 
                ON product_file.identifier = comments_read_by_retailer.product_file_identifier
            LEFT JOIN akeneo_supplier_portal_product_file_comments_read_by_supplier comments_read_by_supplier 
                ON product_file.identifier = comments_read_by_supplier.product_file_identifier
            LEFT JOIN akeneo_supplier_portal_product_file_imported_by_job_execution AS product_file_import
                ON product_file_import.product_file_identifier = product_file.identifier
            WHERE uploaded_by_supplier = :supplierIdentifier
            AND product_file.original_filename LIKE :search
            ORDER BY uploaded_at DESC
            LIMIT :limit
            OFFSET :offset
        SQL;

        return array_map(
            fn (array $file) => new ProductFile(
                $file['identifier'],
                $file['original_filename'],
                $file['path'],
                $file['uploaded_by_contributor'],
                $supplierIdentifier,
                $file['uploaded_at'],
                $file['import_status'],
                $file['retailer_comments']
                    ? \array_filter(\json_decode(
                        $file['retailer_comments'],
                        true,
                    ))
                    : [],
                $file['supplier_comments']
                    ? \array_filter(\json_decode(
                        $file['supplier_comments'],
                        true,
                    ))
                    : [],
                $file['retailer_last_read_at'],
                $file['supplier_last_read_at'],
            ),
            $this->connection->executeQuery(
                $sql,
                [
                    'supplierIdentifier' => $supplierIdentifier,
                    'search' => "%$search%",
                    'offset' => ListProductFilesForSupplier::NUMBER_OF_PRODUCT_FILES_PER_PAGE * ($page - 1),
                    'limit' => ListProductFilesForSupplier::NUMBER_OF_PRODUCT_FILES_PER_PAGE,
                ],
                [
                    'supplierIdentifier' => \PDO::PARAM_STR,
                    'search' => \PDO::PARAM_STR,
                    'offset' => \PDO::PARAM_INT,
                    'limit' => \PDO::PARAM_INT,
                ],
            )->fetchAllAssociative(),
        );
    }
}
