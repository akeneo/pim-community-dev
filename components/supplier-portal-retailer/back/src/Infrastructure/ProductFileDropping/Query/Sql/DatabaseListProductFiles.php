<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFileWithHasUnreadComments;
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
            SELECT 
                product_file.identifier, 
                original_filename,
                uploaded_by_contributor, 
                supplier.label AS supplier, 
                uploaded_at,
                IFNULL(COALESCE(last_comment_read_by_retailer.last_read_at, 0) < MAX(supplier_comments.created_at), 0) AS 'has_unread_comments'
            FROM akeneo_supplier_portal_supplier_product_file AS product_file
            INNER JOIN akeneo_supplier_portal_supplier AS supplier
                ON product_file.uploaded_by_supplier = supplier.identifier
            LEFT JOIN akeneo_supplier_portal_product_file_comments_read_by_retailer AS last_comment_read_by_retailer
                ON last_comment_read_by_retailer.product_file_identifier = product_file.identifier
            LEFT JOIN akeneo_supplier_portal_product_file_supplier_comments AS supplier_comments
                ON supplier_comments.product_file_identifier = product_file.identifier
            GROUP BY product_file.identifier, uploaded_at
            ORDER BY uploaded_at DESC 
            LIMIT :limit
            OFFSET :offset
        SQL;

        return array_map(fn (array $file) => new ProductFileWithHasUnreadComments(
            $file['identifier'],
            $file['original_filename'],
            null,
            $file['uploaded_by_contributor'],
            $file['supplier'],
            $file['uploaded_at'],
            (bool) $file['has_unread_comments'],
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
