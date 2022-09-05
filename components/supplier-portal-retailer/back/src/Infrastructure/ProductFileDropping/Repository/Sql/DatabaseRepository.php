<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Doctrine\DBAL\Connection;

final class DatabaseRepository implements ProductFileRepository
{
    public function __construct(private Connection $connection)
    {
    }

    public function save(ProductFile $productFile): void
    {
        $sql = <<<SQL
            REPLACE INTO `akeneo_supplier_portal_supplier_file` (
                identifier,
                original_filename,
                path,
                uploaded_by_contributor,
                uploaded_by_supplier,
                uploaded_at
            )
            VALUES (:identifier, :original_filename, :path, :uploaded_by_contributor, :uploaded_by_supplier, :uploaded_at)
        SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'identifier' => $productFile->identifier(),
                'original_filename' => $productFile->originalFilename(),
                'path' => $productFile->path(),
                'uploaded_by_contributor' => $productFile->contributorEmail(),
                'uploaded_by_supplier' => $productFile->supplierIdentifier(),
                'uploaded_at' => $productFile->uploadedAt(),
            ],
        );
    }
}
