<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\GetProductFilePathsOfOldProductFiles;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\SupplierFileRepository;
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
            WHERE uploaded_at < :uploadedAt
        SQL;

        return array_map(fn (array $supplierFile) => $supplierFile['path'], $this->connection->executeQuery(
            $sql,
            [
                'uploadedAt' => (new \DateTimeImmutable())->add(
                    \DateInterval::createFromDateString(
                        sprintf(
                            '-%d days',
                            SupplierFileRepository::NUMBER_OF_DAYS_AFTER_WHICH_THE_FILES_ARE_CONSIDERED_OLD,
                        ),
                    ),
                )->format('Y-m-d H:i:s'),
            ],
        )->fetchAllAssociative());
    }
}
