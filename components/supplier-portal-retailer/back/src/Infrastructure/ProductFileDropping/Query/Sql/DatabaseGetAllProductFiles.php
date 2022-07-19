<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\GetAllProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\SupplierFile;
use Doctrine\DBAL\Connection;

final class DatabaseGetAllProductFiles implements GetAllProductFiles
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(int $page = 1): array
    {
        $page = max($page, 1);

        $sql = <<<SQL
            SELECT supplier_file.identifier, filename, path, uploaded_by_contributor, supplier.label AS supplier, uploaded_at, downloaded
            FROM akeneo_supplier_portal_supplier_file supplier_file
            INNER JOIN akeneo_supplier_portal_supplier supplier on supplier_file.uploaded_by_supplier = supplier.identifier
            ORDER BY uploaded_at DESC 
            LIMIT :limit
            OFFSET :offset
        SQL;

        return array_map(fn (array $file) => new SupplierFile(
            $file['identifier'],
            $file['path'],
            (bool) $file['downloaded'],
            $file['uploaded_by_contributor'],
            $file['supplier'],
            $file['uploaded_at'],
        ), $this->connection->executeQuery(
            $sql,
            [
                'offset' => GetAllProductFiles::NUMBER_OF_SUPPLIER_FILES_PER_PAGE * ($page - 1),
                'limit' => GetAllProductFiles::NUMBER_OF_SUPPLIER_FILES_PER_PAGE,
            ],
            [
                'offset' => \PDO::PARAM_INT,
                'limit' => \PDO::PARAM_INT,
            ],
        )->fetchAllAssociative());
    }
}
