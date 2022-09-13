<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\CountProductFileComments;
use Doctrine\DBAL\Connection;

final class DatabaseCountProductFileComments implements CountProductFileComments
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $productFileIdentifier): int
    {
        $sql = <<<SQL
            WITH retailer_comments_count AS (
                SELECT COUNT(*) AS number
                FROM `akeneo_supplier_portal_product_file_retailer_comments`
                WHERE product_file_identifier = :productFileIdentifier
            ),
             supplier_comments_count AS (
                 SELECT COUNT(*) AS number
                 FROM `akeneo_supplier_portal_product_file_supplier_comments`
                 WHERE product_file_identifier = :productFileIdentifier
             )
            SELECT retailer_comments_count.number + supplier_comments_count.number
            FROM retailer_comments_count, supplier_comments_count;
        SQL;

        $numberOfComments = $this->connection->executeQuery(
            $sql,
            ['productFileIdentifier' => $productFileIdentifier],
        )->fetchOne();

        if (false === $numberOfComments) {
            return 0;
        }

        return (int) $numberOfComments;
    }
}
