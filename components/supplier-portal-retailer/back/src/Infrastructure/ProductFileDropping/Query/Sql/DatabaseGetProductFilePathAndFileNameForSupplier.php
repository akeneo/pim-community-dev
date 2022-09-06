<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePathAndFileNameForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFilePathAndFileName;
use Doctrine\DBAL\Connection;

final class DatabaseGetProductFilePathAndFileNameForSupplier implements GetProductFilePathAndFileNameForSupplier
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $productFileIdentifier, string $supplierIdentifier): ?ProductFilePathAndFileName
    {
        $sql = <<<SQL
            SELECT path, original_filename
            FROM akeneo_supplier_portal_supplier_product_file product_file
            WHERE product_file.identifier = :productFileIdentifier
            AND product_file.uploaded_by_supplier = :supplierIdentifier
        SQL;

        $productFile = $this->connection->executeQuery(
            $sql,
            [
                'productFileIdentifier' => $productFileIdentifier,
                'supplierIdentifier' => $supplierIdentifier,
            ],
        )->fetchAssociative();

        if (false === $productFile) {
            return null;
        }

        return new ProductFilePathAndFileName($productFile['original_filename'], $productFile['path']);
    }
}
