<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFileWithMetadataAndComments;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFileWithMetadataAndComments;
use Doctrine\DBAL\Connection;

final class DatabaseGetProductFileWithMetadataAndComments implements GetProductFileWithMetadataAndComments
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $productFileIdentifier): ?ProductFileWithMetadataAndComments
    {
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
                WHERE product_file_identifier = :productFileIdentifier
            ), supplier_comments AS (
                SELECT product_file_identifier, JSON_ARRAYAGG(
                   CASE WHEN content IS NOT NULL THEN JSON_OBJECT(
                           'content', content,
                           'author_email', author_email,
                           'created_at', created_at
                       ) END
               ) AS supplier_comments
                FROM akeneo_supplier_portal_product_file_supplier_comments
                WHERE product_file_identifier = :productFileIdentifier
            )
            SELECT
                product_file.identifier,
                original_filename,
                uploaded_by_contributor,
                uploaded_by_supplier,
                uploaded_at,
                rc.retailer_comments,
                sc.supplier_comments,
                product_file_import.import_status,
                comments_read_by_retailer.last_read_at as retailer_last_read_at,
                product_file_import.finished_at,
                supplier.label AS supplier_label
            FROM akeneo_supplier_portal_supplier_product_file product_file
            LEFT JOIN retailer_comments rc
                ON product_file.identifier = rc.product_file_identifier
            LEFT JOIN supplier_comments sc
                ON product_file.identifier = sc.product_file_identifier
            LEFT JOIN akeneo_supplier_portal_product_file_imported_by_job_execution AS product_file_import
                ON product_file_import.product_file_identifier = product_file.identifier
            LEFT JOIN akeneo_supplier_portal_product_file_comments_read_by_retailer comments_read_by_retailer 
                ON product_file.identifier = comments_read_by_retailer.product_file_identifier
            INNER JOIN akeneo_supplier_portal_supplier supplier
                ON uploaded_by_supplier = supplier.identifier
            WHERE product_file.identifier = :productFileIdentifier;
        SQL;

        $productFileWithComments = $this->connection->executeQuery(
            $sql,
            ['productFileIdentifier' => $productFileIdentifier],
        )->fetchAssociative();

        if (false === $productFileWithComments) {
            return null;
        }

        return new ProductFileWithMetadataAndComments(
            $productFileWithComments['identifier'],
            $productFileWithComments['original_filename'],
            null,
            $productFileWithComments['uploaded_by_contributor'],
            $productFileWithComments['uploaded_by_supplier'],
            $productFileWithComments['uploaded_at'],
            $productFileWithComments['import_status'],
            $productFileWithComments['finished_at'],
            $productFileWithComments['supplier_label'],
            $productFileWithComments['retailer_comments']
                ? \array_filter(\json_decode(
                    $productFileWithComments['retailer_comments'],
                    true,
                ))
                : [],
            $productFileWithComments['supplier_comments']
                ? \array_filter(\json_decode(
                    $productFileWithComments['supplier_comments'],
                    true,
                ))
                : [],
            $productFileWithComments['retailer_last_read_at'],
        );
    }
}
