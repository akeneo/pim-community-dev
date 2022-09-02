<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetIdentifierFromCode;
use Doctrine\DBAL\Connection;

final class DatabaseGetIdentifierFromCode implements GetIdentifierFromCode
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $code): ?string
    {
        $identifier = $this->connection->executeQuery(
            <<<SQL
                SELECT identifier
                FROM `akeneo_supplier_portal_supplier`
                WHERE code = :code
            SQL
            ,
            [
                'code' => $code,
            ],
        )->fetchOne();

        return false !== $identifier ? $identifier : null;
    }
}
