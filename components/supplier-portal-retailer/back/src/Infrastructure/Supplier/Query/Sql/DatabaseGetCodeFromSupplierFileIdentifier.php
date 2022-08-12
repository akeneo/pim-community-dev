<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetCodeFromSupplierFileIdentifier;
use Doctrine\DBAL\Connection;

final class DatabaseGetCodeFromSupplierFileIdentifier implements GetCodeFromSupplierFileIdentifier
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $supplierFileIdentifier): ?string
    {
        $code = $this->connection->executeQuery(
            <<<SQL
                SELECT supplier.code
                FROM `akeneo_supplier_portal_supplier` supplier
                INNER JOIN akeneo_supplier_portal_supplier_file supplier_file 
                    ON supplier_file.uploaded_by_supplier = supplier.identifier 
                WHERE supplier_file.identifier = :supplierFileIdentifier
            SQL
            ,
            [
                'supplierFileIdentifier' => $supplierFileIdentifier,
            ],
        )->fetchOne();

        return false !== $code ? $code : null;
    }
}
