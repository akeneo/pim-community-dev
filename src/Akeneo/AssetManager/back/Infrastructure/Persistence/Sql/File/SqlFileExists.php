<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\File;

use Akeneo\AssetManager\Domain\Query\File\FileExistsInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

class SqlFileExists implements FileExistsInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function exists(string $fileKey): bool
    {
        $sql = <<<SQL
          SELECT EXISTS(
              SELECT 1 FROM akeneo_file_storage_file_info WHERE file_key = :file_key
          ) as is_existing
SQL;

        $statement = $this->connection->executeQuery($sql, ['file_key' => $fileKey]);
        $platform = $this->connection->getDatabasePlatform();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        return Type::getType(Type::BOOLEAN)->convertToPhpValue($result['is_existing'], $platform);
    }
}
