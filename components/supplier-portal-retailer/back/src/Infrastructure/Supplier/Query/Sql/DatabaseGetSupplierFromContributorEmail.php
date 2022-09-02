<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierFromContributorEmail;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Doctrine\DBAL\Connection;

final class DatabaseGetSupplierFromContributorEmail implements GetSupplierFromContributorEmail
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $contributorEmail): ?Supplier
    {
        $supplier = $this->connection->executeQuery(
            <<<SQL
                SELECT identifier, code, label
                FROM `akeneo_supplier_portal_supplier` supplier
                INNER JOIN `akeneo_supplier_portal_supplier_contributor` contributor 
                    ON contributor.supplier_identifier = supplier.identifier
                WHERE contributor.email = :contributorEmail
            SQL
            ,
            [
                'contributorEmail' => $contributorEmail,
            ],
        )->fetchAssociative();

        return false !== $supplier ? new Supplier(
            $supplier['identifier'],
            $supplier['code'],
            $supplier['label'],
        ) : null;
    }
}
