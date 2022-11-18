<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Doctrine\DBAL\Connection;

final class DatabaseComputeCommentsReadDelay
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(\DateTimeImmutable $date, string $productFileIdentifier): array
    {
        $sql = <<<SQL
            SELECT TIMESTAMPDIFF(SECOND , created_at, :date) AS read_delay
            FROM akeneo_supplier_portal_product_file_retailer_comments AS retailer_comments
            LEFT JOIN akeneo_supplier_portal_product_file_comments_read_by_supplier read_by_supplier
                ON retailer_comments.product_file_identifier = read_by_supplier.product_file_identifier
            WHERE retailer_comments.product_file_identifier = :productFileIdentifier
            AND (created_at > read_by_supplier.last_read_at OR last_read_at IS NULL);
SQL;
        $results = $this->connection->executeQuery($sql, [
            'date' => $date->format('Y-m-d H:i:s'),
            'productFileIdentifier' => $productFileIdentifier,
        ])->fetchAllAssociative();

        return array_map(fn (array $item) => (int) $item['read_delay'], $results);
    }
}
