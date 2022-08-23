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
                'uploaded_by_contributor' => $supplierFile->contributorEmail(),
                'uploaded_by_supplier' => $supplierFile->supplierIdentifier(),
                'uploaded_at' => $supplierFile->uploadedAt(),
            ],
        );
    }

    public function deleteOld(): void
    {
        $sql = <<<SQL
            DELETE FROM akeneo_supplier_portal_supplier_file
            WHERE uploaded_at < :uploadedAt
        SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'uploadedAt' => (new \DateTimeImmutable())->add(
                    \DateInterval::createFromDateString(
                        sprintf(
                            '-%d days',
                            self::NUMBER_OF_DAYS_AFTER_WHICH_THE_FILES_ARE_CONSIDERED_OLD,
                        ),
                    ),
                )->format('Y-m-d H:i:s'),
            ],
        );
    }
}
