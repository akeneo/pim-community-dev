<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetAllProductFilesCount;
use Doctrine\DBAL\Connection;

final class DatabaseGetAllProductFilesCount implements GetAllProductFilesCount
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(): int
    {
        return (int) $this->connection->executeQuery(
            <<<SQL
            SELECT COUNT(*)
            FROM `akeneo_supplier_portal_supplier_product_file`
        SQL,
        )->fetchOne();
    }
}
