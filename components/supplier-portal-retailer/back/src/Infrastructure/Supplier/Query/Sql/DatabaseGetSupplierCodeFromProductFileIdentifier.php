<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierCodeFromProductFileIdentifier;
use Doctrine\DBAL\Connection;

final class DatabaseGetSupplierCodeFromProductFileIdentifier implements GetSupplierCodeFromProductFileIdentifier
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $productFileIdentifier): ?string
    {
        $code = $this->connection->executeQuery(
            <<<SQL
                SELECT supplier.code
                FROM `akeneo_supplier_portal_supplier` supplier
                INNER JOIN akeneo_supplier_portal_supplier_product_file product_file
                    ON product_file.uploaded_by_supplier = supplier.identifier 
                WHERE product_file.identifier = :productFileIdentifier
            SQL
            ,
            [
                'productFileIdentifier' => $productFileIdentifier,
            ],
        )->fetchOne();

        return false !== $code ? $code : null;
    }
}
