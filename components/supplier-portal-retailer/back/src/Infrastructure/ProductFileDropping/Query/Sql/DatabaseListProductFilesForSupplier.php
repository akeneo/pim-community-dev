<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListProductFilesForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\SupplierFile;
use Doctrine\DBAL\Connection;

final class DatabaseListProductFilesForSupplier implements ListProductFilesForSupplier
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(
        string $supplierIdentifier, // @todo Create a query that gets the supplier identifier from a contributor email
        int $page = 1,
        int $numberOfSupplierFilesPerPage = ListProductFilesForSupplier::NUMBER_OF_SUPPLIER_FILES_PER_PAGE,
    ): array {
        $page = max($page, 1);

        $sql = <<<SQL
            SELECT supplier_file.identifier, original_filename, path, uploaded_by_contributor, uploaded_at
            FROM akeneo_supplier_portal_supplier_file supplier_file
            where uploaded_by_supplier = :supplierIdentifier
            ORDER BY uploaded_at DESC 
            LIMIT :limit
            OFFSET :offset
        SQL;

        return array_map(fn (array $file) => new SupplierFile(
            $file['identifier'],
            $file['original_filename'],
            $file['path'],
            $file['uploaded_by_contributor'],
            $supplierIdentifier,
            $file['uploaded_at'],
        ), $this->connection->executeQuery(
            $sql,
            [
                'supplierIdentifier' => $supplierIdentifier,
                'offset' => ListProductFilesForSupplier::NUMBER_OF_SUPPLIER_FILES_PER_PAGE * ($page - 1),
                'limit' => ListProductFilesForSupplier::NUMBER_OF_SUPPLIER_FILES_PER_PAGE,
            ],
            [
                'supplierIdentifier' => \PDO::PARAM_STR,
                'offset' => \PDO::PARAM_INT,
                'limit' => \PDO::PARAM_INT,
            ],
        )->fetchAllAssociative());
    }
}
