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

use Akeneo\AssetManager\Domain\Query\File\FindFileDataByFileKeyInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

class SqlFindFileDataByFileKey implements FindFileDataByFileKeyInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function find(string $fileKey): ?array
    {
        $sql = <<<SQL
          SELECT file_key, original_filename, size, mime_type, extension
          FROM akeneo_file_storage_file_info
          WHERE file_key = :file_key
SQL;

        $statement = $this->connection->executeQuery($sql, ['file_key' => $fileKey]);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        if (false === $result) {
            return null;
        }

        $platform = $this->connection->getDatabasePlatform();

        return [
            'filePath'         => Type::getType(Type::STRING)->convertToPhpValue($result['file_key'], $platform),
            'originalFilename' => Type::getType(Type::STRING)->convertToPhpValue($result['original_filename'], $platform),
            'size'             => Type::getType(Type::INTEGER)->convertToPhpValue($result['size'], $platform),
            'mimeType'         => Type::getType(Type::STRING)->convertToPhpValue($result['mime_type'], $platform),
            'extension'        => Type::getType(Type::STRING)->convertToPhpValue($result['extension'], $platform),
        ];
    }
}
