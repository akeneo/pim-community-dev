<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\GetSupplierFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\SupplierFile;
use Doctrine\DBAL\Connection;

final class DatabaseGetSupplierFiles implements GetSupplierFiles
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(string $supplierIdentifier, int $page = 1): array
    {
        $page = max($page, 1);

        $sql = <<<SQL
            SELECT supplier_file.identifier, path, uploaded_by_contributor, uploaded_at
            FROM akeneo_supplier_portal_supplier_file supplier_file
            where uploaded_by_supplier = :supplierIdentifier
            ORDER BY uploaded_at DESC 
            LIMIT :limit
            OFFSET :offset
        SQL;

        return array_map(fn (array $file) => new SupplierFile(
            $file['identifier'],
            $file['path'],
            $file['uploaded_by_contributor'],
            $supplierIdentifier,
            $file['uploaded_at'],
        ), $this->connection->executeQuery(
            $sql,
            [
                'supplierIdentifier' => $supplierIdentifier,
                'offset' => GetSupplierFiles::NUMBER_OF_SUPPLIER_FILES_PER_PAGE * ($page - 1),
                'limit' => GetSupplierFiles::NUMBER_OF_SUPPLIER_FILES_PER_PAGE,
            ],
            [
                'supplierIdentifier' => \PDO::PARAM_STR,
                'offset' => \PDO::PARAM_INT,
                'limit' => \PDO::PARAM_INT,
            ],
        )->fetchAllAssociative());
    }
}
