<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetSupplierProductFilesCount;
use Doctrine\DBAL\Connection;

final class DatabaseGetSupplierProductFilesCount implements GetSupplierProductFilesCount
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $supplierIdentifier, string $search = ''): int
    {
        return (int) $this->connection->executeQuery(
            <<<SQL
            SELECT COUNT(*)
            FROM `akeneo_supplier_portal_supplier_product_file`
            WHERE uploaded_by_supplier = :supplierIdentifier
            AND akeneo_supplier_portal_supplier_product_file.original_filename LIKE :search
        SQL,
            [
                'supplierIdentifier' => $supplierIdentifier,
                'search' => "%$search%",
            ],
            [
                'supplierIdentifier' => \PDO::PARAM_STR,
                'search' => \PDO::PARAM_STR,
            ],
        )->fetchOne();
    }
}
