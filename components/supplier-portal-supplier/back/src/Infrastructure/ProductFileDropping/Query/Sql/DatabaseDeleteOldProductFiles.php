<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\DeleteOldProductFiles;
use Doctrine\DBAL\Connection;

final class DatabaseDeleteOldProductFiles implements DeleteOldProductFiles
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(): void
    {
        $sql = <<<SQL
            DELETE FROM akeneo_supplier_portal_supplier_file
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
