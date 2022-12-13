<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilesWithUnreadCommentsForContributor;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFileWithUnreadComments;
use Doctrine\DBAL\Connection;

final class DatabaseGetProductFilesWithUnreadCommentsForContributor implements GetProductFilesWithUnreadCommentsForContributor
{
    private const COMMENTS_AGE_IN_HOURS = 24;

    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $contributorEmail): array
    {
        $sql = <<<SQL
            WITH retailer_comments AS (
                SELECT rc.product_file_identifier, JSON_ARRAYAGG(
                        CASE WHEN content IS NOT NULL THEN JSON_OBJECT(
                                'content', content,
                                'author_email', author_email
                            ) END
                    ) AS retailer_comments
                FROM akeneo_supplier_portal_product_file_retailer_comments AS rc
                         LEFT JOIN akeneo_supplier_portal_product_file_comments_read_by_supplier comments_read_by_supplier
                                   ON rc.product_file_identifier = comments_read_by_supplier.product_file_identifier
                WHERE TIMESTAMPDIFF(HOUR, rc.created_at, NOW()) < :COMMENTS_AGE_IN_HOURS
                  AND (rc.created_at > comments_read_by_supplier.last_read_at OR comments_read_by_supplier.last_read_at IS NULL)
                GROUP BY rc.product_file_identifier
            ) SELECT
                  product_file.identifier,
                  original_filename,
                  path,
                  rc.retailer_comments
            FROM akeneo_supplier_portal_supplier_product_file product_file
                     LEFT JOIN retailer_comments rc
                               ON identifier = rc.product_file_identifier
                     LEFT JOIN akeneo_supplier_portal_product_file_comments_read_by_supplier comments_read_by_supplier
                               ON product_file.identifier = comments_read_by_supplier.product_file_identifier
            WHERE uploaded_by_contributor = :contributorEmail
            AND rc.retailer_comments IS NOT NULL
            ORDER BY uploaded_at DESC
        SQL;

        return array_map(
            fn (array $productFileWithNewComments) => new ProductFileWithUnreadComments(
                $productFileWithNewComments['identifier'],
                $productFileWithNewComments['original_filename'],
                $productFileWithNewComments['path'],
                $productFileWithNewComments['retailer_comments']
                ? \array_filter(\json_decode(
                    $productFileWithNewComments['retailer_comments'],
                    true,
                ))
                : [],
            ),
            $this->connection->executeQuery(
                $sql,
                ['contributorEmail' => $contributorEmail,
                'COMMENTS_AGE_IN_HOURS' => self::COMMENTS_AGE_IN_HOURS, ],
            )->fetchAllAssociative(),
        );
    }
}
