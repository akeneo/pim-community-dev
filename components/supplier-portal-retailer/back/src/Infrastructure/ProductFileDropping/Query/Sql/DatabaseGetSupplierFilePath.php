<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\GetSupplierFilePath;
use Doctrine\DBAL\Connection;

final class DatabaseGetSupplierFilePath implements GetSupplierFilePath
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $supplierFileIdentifier): ?string
    {
        $sql = <<<SQL
            SELECT path 
            FROM akeneo_supplier_portal_supplier_file supplier_file 
            WHERE identifier = :identifier
        SQL;

        $path = $this->connection->executeQuery(
            $sql,
            [
                'identifier' => $supplierFileIdentifier,
            ],
            [
                'identifier' => \PDO::PARAM_STR,
            ],
        )->fetchOne();

        if (false === $path) {
            return null;
        }

        return $path;
    }
}
