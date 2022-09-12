<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFileWithComments;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile;
use Doctrine\DBAL\Connection;

final class DatabaseGetProductFileWithComments implements GetProductFileWithComments
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $productFileIdentifier): ?ProductFile
    {
        $sql = <<<SQL
            SELECT
                identifier,
                original_filename,
                uploaded_by_contributor,
                uploaded_by_supplier,
                uploaded_at,
                JSON_ARRAYAGG(
                    CASE WHEN rc.content IS NOT NULL THEN JSON_OBJECT(
                        'content', rc.content,
                        'author_email', rc.author_email,
                        'created_at', rc.created_at
                    ) END
                ) AS retailer_comments,
                JSON_ARRAYAGG(
                    CASE WHEN sc.content IS NOT NULL THEN JSON_OBJECT(
                        'content', sc.content,
                        'author_email', sc.author_email,
                        'created_at', sc.created_at
                    ) END
                ) AS supplier_comments
            FROM akeneo_supplier_portal_supplier_product_file
                LEFT JOIN akeneo_supplier_portal_product_file_retailer_comments rc
                    ON identifier = rc.product_file_identifier
                LEFT JOIN akeneo_supplier_portal_product_file_supplier_comments sc
                    ON identifier = sc.product_file_identifier
            WHERE identifier = :productFileIdentifier;
        SQL;

        $productFileWithComments = $this->connection->executeQuery(
            $sql,
            ['productFileIdentifier' => $productFileIdentifier],
        )->fetchAssociative();

        if (null === $productFileWithComments['identifier']) {
            return null;
        }

        return new ProductFile(
            $productFileWithComments['identifier'],
            $productFileWithComments['original_filename'],
            $productFileWithComments['uploaded_by_contributor'],
            $productFileWithComments['uploaded_by_supplier'],
            $productFileWithComments['uploaded_at'],
            $productFileWithComments['retailer_comments']
                ? array_filter(\json_decode($productFileWithComments['retailer_comments'], true))
                : [],
            $productFileWithComments['supplier_comments']
                ? array_filter(\json_decode($productFileWithComments['supplier_comments'], true))
                : [],
        );
    }
}
