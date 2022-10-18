<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\MarkCommentsAsReadBySupplier;
use Doctrine\DBAL\Connection;

final class DatabaseMarkCommentsAsReadBySupplier implements MarkCommentsAsReadBySupplier
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $productFileIdentifier, \DateTimeInterface $readAt): void
    {
        $sql = <<<SQL
            REPLACE INTO akeneo_supplier_portal_product_file_comments_read_by_supplier (product_file_identifier, last_read_at)
            VALUES (:productFileIdentifier, :lastReadAt)
        SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'productFileIdentifier' => $productFileIdentifier,
                'lastReadAt' => $readAt->format('Y-m-d H:i:s'),
            ],
        );
    }
}