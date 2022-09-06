<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\DeleteOldProductFiles;
use Doctrine\DBAL\Connection;

final class DatabaseDeleteOldProductFiles implements DeleteOldProductFiles
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(): void
    {
        $sql = <<<SQL
            DELETE FROM akeneo_supplier_portal_supplier_product_file
            WHERE uploaded_at < :retentionLimit
        SQL;

        $this->connection->executeStatement(
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
        );
    }
}
