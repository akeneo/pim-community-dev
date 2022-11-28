<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\CountFilteredProductFiles;
use Doctrine\DBAL\Connection;

final class DatabaseCountFilteredProductFiles implements CountFilteredProductFiles
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(string $search = ''): int
    {
        return (int) $this->connection->executeQuery(
            <<<SQL
            SELECT COUNT(*)
            FROM `akeneo_supplier_portal_supplier_product_file`
            WHERE original_filename LIKE :search
        SQL,
            [
                'search' => "%$search%",
            ],
            [
                'search' => \PDO::PARAM_STR,
            ],
        )->fetchOne();
    }
}
