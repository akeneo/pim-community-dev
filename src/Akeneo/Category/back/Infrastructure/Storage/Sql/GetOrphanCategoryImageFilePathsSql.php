<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\DTO\IteratorStatus;
use Akeneo\Category\Domain\ImageFile\GetOrphanCategoryImageFilePaths;
use Akeneo\Category\Domain\ImageFile\Storage;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\ImageValue;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetOrphanCategoryImageFilePathsSql implements GetOrphanCategoryImageFilePaths
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(): iterable
    {
        $this->connection->executeStatement(
            <<<SQL
                CREATE TEMPORARY TABLE `pim_catalog_category_file_info_tmp` (
                    file_key VARCHAR(255) NOT NULL,
                    UNIQUE KEY file_key (file_key)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC
            SQL
        );

        foreach ($this->getImageValues() as $imageValue) {
            $filePath = $imageValue->getValue()->getFilePath();

            $this->connection->executeStatement(
                <<<SQL
                    INSERT IGNORE INTO `pim_catalog_category_file_info_tmp`
                    SET file_key = :file_key
                SQL,
                [
                    'file_key' => $filePath,
                ],
                [
                    'file_key' => \PDO::PARAM_STR,
                ],
            );

            yield IteratorStatus::inProgress();
        }

        $result = $this->connection->executeQuery(
            <<<SQL
                SELECT file_info.file_key
                FROM `akeneo_file_storage_file_info` as file_info
                LEFT JOIN `pim_catalog_category_file_info_tmp` as tmp
                    ON tmp.file_key = file_info.file_key
                WHERE file_info.storage = :category_storage
                AND tmp.file_key IS NULL
            SQL,
            [
                'category_storage' => Storage::CATEGORY_STORAGE_ALIAS,
            ],
            [
                'category_storage' => \PDO::PARAM_STR,
            ],
        )->fetchFirstColumn();

        yield IteratorStatus::done($result);
    }

    /**
     * @return iterable<ImageValue>
     */
    private function getImageValues(): iterable
    {
        $offset = 0;
        while (true) {
            $result = $this->connection->executeQuery(
                <<<SQL
                    SELECT value_collection
                    FROM pim_catalog_category
                    WHERE value_collection IS NOT NULL
                    ORDER BY id ASC
                    LIMIT 1000 OFFSET :offset
                SQL,
                [
                    'offset' => $offset,
                ],
                [
                    'offset' => \PDO::PARAM_INT,
                ],
            );
            $offset += 1000;

            $rawValueCollections = $result->fetchFirstColumn();
            if (empty($rawValueCollections)) {
                return;
            }

            foreach ($rawValueCollections as $rawValueCollection) {
                $valueCollection = ValueCollection::fromDatabase(
                    \json_decode($rawValueCollection, true, 512, \JSON_THROW_ON_ERROR),
                );
                foreach ($valueCollection->getValues() as $value) {
                    if ($value instanceof ImageValue) {
                        yield $value;
                    }
                }
            }
        }
    }
}
