<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Repository\Sql;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Model\SupplierFile;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\SupplierFileRepository;
use Doctrine\DBAL\Connection;

final class DatabaseRepository implements SupplierFileRepository
{
    public function __construct(private Connection $connection)
    {
    }

    public function save(SupplierFile $supplierFile): void
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
                'identifier' => $supplierFile->identifier(),
                'original_filename' => $supplierFile->originalFilename(),
                'path' => $supplierFile->path(),
                'uploaded_by_contributor' => $supplierFile->uploadedByContributor(),
                'uploaded_by_supplier' => $supplierFile->uploadedBySupplier(),
                'uploaded_at' => $supplierFile->uploadedAt(),
            ],
        );
    }
}
