<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Cli;

use Akeneo\Category\Domain\ValueObject\Attribute\Value\ImageValue;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsCommand(
    name: 'purge-orphan-category-image-files',
)]
class PurgeOrphanCategoryImageFilesCommand extends Command
{
    public function __construct(
        private readonly Connection $connection,
        private readonly FilesystemProvider $filesystemProvider,
        private readonly FileInfoRepositoryInterface $fileInfoRepositoryInterface,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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
        }

        $result = $this->connection->executeQuery(
            <<<SQL
                SELECT file_info.file_key
                FROM `akeneo_file_storage_file_info` as file_info
                LEFT JOIN `pim_catalog_category_file_info_tmp` as tmp
                    ON tmp.file_key = file_info.file_key
                WHERE file_info.storage = 'categoryStorage'
                AND tmp.file_key IS NULL
            SQL
        );

        $fs = $this->filesystemProvider->getFilesystem('categoryStorage');
        foreach ($result->fetchFirstColumn() as $filePath) {
            // if ($fs->lastModified($filePath) > time() - 86400 /* 1 day */) {
            if ($fs->lastModified($filePath) > time() - 600) {
                $output->writeln(sprintf('Skipping file "%s", too recent', $filePath));
                continue;
            }

            $output->writeln(sprintf('Deleting file "%s"', $filePath));
            $fs->delete($filePath);
            $this->deleteFileInfo($filePath);
        }

        return Command::SUCCESS;
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
                ]
            );
            $offset += 1000;

            $rawValueCollections = $result->fetchFirstColumn();
            if (empty($rawValueCollections)) {
                return;
            }

            foreach ($rawValueCollections as $rawValueCollection) {
                $valueCollection = ValueCollection::fromDatabase(
                    \json_decode($rawValueCollection, true, 512, \JSON_THROW_ON_ERROR)
                );
                foreach ($valueCollection->getValues() as $value) {
                    if ($value instanceof ImageValue) {
                        yield $value;
                    }
                }
            }
        };
    }

    private function deleteFileInfo(string $filePath): void
    {
        $this->connection->executeStatement(
            <<<SQL
                DELETE FROM `akeneo_file_storage_file_info`
                WHERE file_key = :file_key
            SQL,
            [
                'file_key' => $filePath,
            ],
            [
                'file_key' => \PDO::PARAM_STR,
            ],
        );
    }
}
