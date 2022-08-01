<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\GetSupplierFilesCount;
use Doctrine\DBAL\Connection;

final class DatabaseGetSupplierFilesCount implements GetSupplierFilesCount
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $supplierIdentifier): int
    {
        return (int) $this->connection->executeQuery(
            <<<SQL
            SELECT COUNT(*)
            FROM `akeneo_supplier_portal_supplier_file`
            WHERE uploaded_by_supplier = :supplierIdentifier
        SQL,
            [
                'supplierIdentifier' => $supplierIdentifier,
            ],
            [
                'supplierIdentifier' => \PDO::PARAM_STR,
            ],
        )->fetchOne();
    }
}
