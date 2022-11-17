<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListSupplierProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFileWithHasUnreadComments;
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
            SELECT
                product_file.identifier,
                original_filename,
                uploaded_by_contributor,
                uploaded_at,
                IFNULL(COALESCE(last_comment_read_by_retailer.last_read_at, 0) < MAX(supplier_comments.created_at), 0) AS 'has_unread_comments',
                product_file_import.import_status
            FROM akeneo_supplier_portal_supplier_product_file AS product_file
            LEFT JOIN akeneo_supplier_portal_product_file_comments_read_by_retailer AS last_comment_read_by_retailer
                ON last_comment_read_by_retailer.product_file_identifier = product_file.identifier
            LEFT JOIN akeneo_supplier_portal_product_file_supplier_comments AS supplier_comments
                ON supplier_comments.product_file_identifier = product_file.identifier
            LEFT JOIN akeneo_supplier_portal_product_file_imported_by_job_execution AS product_file_import
                ON product_file_import.product_file_identifier = product_file.identifier
            WHERE uploaded_by_supplier = :supplierIdentifier
            GROUP BY product_file.identifier, uploaded_at, product_file_import.import_status
            ORDER BY uploaded_at DESC
            LIMIT :limit
            OFFSET :offset
        SQL;

        return array_map(fn (array $file) => new ProductFileWithHasUnreadComments(
            $file['identifier'],
            $file['original_filename'],
            null,
            $file['uploaded_by_contributor'],
            $supplierIdentifier,
            $file['uploaded_at'],
            (bool) $file['has_unread_comments'],
            $file['import_status'],
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
