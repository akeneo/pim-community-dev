<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierLabelFromIdentifier;
use Doctrine\DBAL\Connection;

final class DatabaseGetSupplierLabelFromIdentifier implements GetSupplierLabelFromIdentifier
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(string $identifier): ?string
    {
        $supplierName = $this->connection->executeQuery(
            <<<SQL
                SELECT label
                FROM `akeneo_supplier_portal_supplier`
                WHERE identifier = :identifier
            SQL
            ,
            [
                'identifier' => $identifier,
            ],
        )->fetchOne();

        return false !== $supplierName ? $supplierName : null;
    }
}
