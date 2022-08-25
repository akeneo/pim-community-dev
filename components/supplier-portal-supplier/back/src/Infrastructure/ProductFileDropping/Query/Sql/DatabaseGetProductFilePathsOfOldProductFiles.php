<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\DeleteOldProductFiles;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\GetProductFilePathsOfOldProductFiles;
use Doctrine\DBAL\Connection;

final class DatabaseGetProductFilePathsOfOldProductFiles implements GetProductFilePathsOfOldProductFiles
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(): array
    {
        $sql = <<<SQL
            SELECT path
            FROM `akeneo_supplier_portal_supplier_file`
            WHERE uploaded_at < :retentionLimit
        SQL;

        return array_map(fn (array $supplierFile) => $supplierFile['path'], $this->connection->executeQuery(
            $sql,
            [
                'retentionLimit' => (new \DateTimeImmutable())->add(
                    \DateInterval::createFromDateString(
                        sprintf(
                            '-%d days',
                            DeleteOldProductFiles::RETENTION_DURATION_IN_DAYS,
                        ),
                    ),
                )->format('Y-m-d H:i:s'),
            ],
        )->fetchAllAssociative());
    }
}
