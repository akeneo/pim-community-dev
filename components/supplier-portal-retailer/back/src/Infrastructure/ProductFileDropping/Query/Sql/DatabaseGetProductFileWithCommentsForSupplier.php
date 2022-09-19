<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFileWithCommentsForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile;
use Doctrine\DBAL\Connection;

final class DatabaseGetProductFileWithCommentsForSupplier implements GetProductFileWithCommentsForSupplier
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $productFileIdentifier): ?ProductFile
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
                identifier,
                original_filename,
                path,
                uploaded_by_contributor,
                uploaded_by_supplier,
                uploaded_at,
                rc.retailer_comments,
                sc.supplier_comments
            FROM akeneo_supplier_portal_supplier_product_file
            LEFT JOIN retailer_comments rc
                ON identifier = rc.product_file_identifier
            LEFT JOIN supplier_comments sc
                ON identifier = sc.product_file_identifier
            WHERE identifier = :productFileIdentifier;
        SQL;

        $productFileWithComments = $this->connection->executeQuery(
            $sql,
            ['productFileIdentifier' => $productFileIdentifier],
        )->fetchAssociative();

        if (false === $productFileWithComments) {
            return null;
        }

        return new ProductFile(
            $productFileWithComments['identifier'],
            $productFileWithComments['original_filename'],
            $productFileWithComments['path'],
            $productFileWithComments['uploaded_by_contributor'],
            $productFileWithComments['uploaded_by_supplier'],
            $productFileWithComments['uploaded_at'],
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
        );
    }
}
