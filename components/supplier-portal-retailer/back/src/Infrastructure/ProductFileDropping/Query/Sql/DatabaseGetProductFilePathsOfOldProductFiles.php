<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\DeleteOldProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePathsOfOldProductFiles;
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
            FROM `akeneo_supplier_portal_supplier_product_file`
            WHERE uploaded_at < :retentionLimit
        SQL;

        return array_map(fn (array $productFile) => $productFile['path'], $this->connection->executeQuery(
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
