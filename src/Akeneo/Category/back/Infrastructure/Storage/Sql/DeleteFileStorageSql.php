<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\DeleteFileStorage;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteFileStorageSql implements DeleteFileStorage
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(string $fileKey): void
    {
        $query = <<< SQL
            DELETE FROM akeneo_file_storage_file_info
            WHERE file_key = :file_key
        SQL;

        $this->connection->executeQuery(
            $query,
            [
                'file_key' => $fileKey,
            ],
            [
                'file_key' => \PDO::PARAM_STR,
            ],
        );
    }
}
