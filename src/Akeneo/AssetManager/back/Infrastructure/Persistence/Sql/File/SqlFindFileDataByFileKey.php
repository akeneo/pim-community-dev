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
use Doctrine\DBAL\Types\Types;

class SqlFindFileDataByFileKey implements FindFileDataByFileKeyInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function find(string $fileKey): ?array
    {
        $sql = <<<SQL
          SELECT file_key, original_filename, size, mime_type, extension
          FROM akeneo_file_storage_file_info
          WHERE file_key = :file_key
SQL;

        $statement = $this->connection->executeQuery($sql, ['file_key' => $fileKey]);
        $result = $statement->fetchAssociative();

        if (false === $result) {
            return null;
        }

        $platform = $this->connection->getDatabasePlatform();

        return [
            'filePath'         => Type::getType(Types::STRING)->convertToPhpValue($result['file_key'], $platform),
            'originalFilename' => Type::getType(Types::STRING)->convertToPhpValue($result['original_filename'], $platform),
            'size'             => Type::getType(Types::INTEGER)->convertToPhpValue($result['size'], $platform),
            'mimeType'         => Type::getType(Types::STRING)->convertToPhpValue($result['mime_type'], $platform),
            'extension'        => Type::getType(Types::STRING)->convertToPhpValue($result['extension'], $platform),
        ];
    }
}
