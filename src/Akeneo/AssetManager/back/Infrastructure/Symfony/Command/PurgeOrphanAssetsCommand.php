<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Symfony\Command;

use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Doctrine\DBAL\Connection;
use League\Flysystem\FileNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * This command will identify all the file info lines not linked to an asset anymore and remove them. This command is not intended to be used as a crontask.
 * Use it as a maintenance tool
 *
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeOrphanAssetsCommand extends Command
{
    protected static $defaultName = self::PURGE_ORPHANS_FILE_INFO_COMMAND_NAME;

    public const PURGE_ORPHANS_FILE_INFO_COMMAND_NAME = 'akeneo:asset-manager:purge-orphans-file-info';

    private Connection $connection;

    private FilesystemProvider $filesystemProvider;

    public function __construct(Connection $connection, FilesystemProvider $filesystemProvider)
    {
        parent::__construct();

        $this->connection = $connection;
        $this->filesystemProvider = $filesystemProvider;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Identify all the file info lines not linked to an asset anymore and remove them.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->preparePurgeTable();

        $this->removeAllFilesFromPurgeTable();

        $filesToPurge = $this->countFilesToPurge();
        $output->writeln(sprintf('Files to purge: %d', $filesToPurge));

        if ($filesToPurge > 0) {
            $io = new SymfonyStyle($input, $output);
            if (!$io->confirm(sprintf('We found %s asset to purge. Do you want to continue and remove the files from storage?', $filesToPurge))) {
                return;
            }
            $this->removeFileFromStorage($output);
            $this->removeFilesFromFileInfo();
        } else {
            $output->writeln('Nothing to do. End of the process');
        }

        $this->removePurgeTable();
    }

    private function preparePurgeTable()
    {
        $sqlQuery = <<<SQL
            DROP TABLE IF EXISTS `akeneo_asset_manager_file_info_purge`;

            CREATE TEMPORARY TABLE `akeneo_asset_manager_file_info_purge` LIKE `akeneo_file_storage_file_info`;
            INSERT `akeneo_asset_manager_file_info_purge` SELECT * FROM `akeneo_file_storage_file_info` WHERE storage='assetStorage';
        SQL;

        $this->connection->exec($sqlQuery);
    }

    private function removeAllFilesFromPurgeTable()
    {
        $assetFilesPages = $this->getAllAssetFiles();
        foreach ($assetFilesPages as $files) {
            $this->removeFilesFromPurgeTable($files);
        }
    }

    private function getAllAssetFiles(): \Iterator
    {
        $formerIdentifier = '';
        $sqlQuery = <<<SQL
            SELECT identifier, filePath
            FROM akeneo_asset_manager_asset asset, json_table(json_extract(asset.value_collection,'$.*.data.filePath'),
                '$[*]' COLUMNS(filePath varchar(255) PATH '$')) as filePath
            WHERE asset.identifier > :formerIdentifier
            ORDER BY asset.identifier ASC
            LIMIT :limit;
        SQL;

        while (true) {
            $rows = $this->connection->executeQuery(
                $sqlQuery,
                [
                    'formerIdentifier' => $formerIdentifier,
                    'limit' => 1000,
                ],
                [
                    'formerIdentifier' => \PDO::PARAM_STR,
                    'limit' => \PDO::PARAM_INT,
                ]
            )->fetchAll();

            if (empty($rows)) {
                return;
            }

            $formerIdentifier = (string) end($rows)['identifier'];
            yield array_column($rows, 'filePath');
        }
    }

    private function removeFilesFromPurgeTable(array $fileKeys)
    {
        $sqlQuery = <<<SQL
            DELETE FROM `akeneo_asset_manager_file_info_purge` WHERE file_key IN (:fileKeys);
        SQL;

        $this->connection->executeUpdate(
            $sqlQuery,
            [
                'fileKeys' => $fileKeys,
            ],
            [
                'fileKeys' => Connection::PARAM_STR_ARRAY
            ]
        );
    }

    private function countFilesToPurge()
    {
        $sqlQuery = <<<SQL
            SELECT count(*)
            FROM `akeneo_asset_manager_file_info_purge`;
        SQL;

        $statement = $this->connection->executeQuery(
            $sqlQuery
        );

        return (int) $statement->fetchColumn();
    }

    private function removeFileFromStorage(OutputInterface $output)
    {
        $fs = $this->filesystemProvider->getFilesystem('assetStorage');

        $sqlQuery = <<<SQL
            SELECT file_to_purge.file_key as file_key
            FROM `akeneo_asset_manager_file_info_purge` as file_to_purge
        SQL;

        $statement = $this->connection->executeQuery($sqlQuery);

        $progressBar = new ProgressBar($output, $this->countFilesToPurge());
        $progressBar->start();
        $fileRemoved = 0;
        while (false !== $result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            try {
                $fs->delete($result['file_key']);
            } catch (FileNotFoundException $exception) {
                $output->writeln(sprintf('File "%s" not found in storage', $exception->getMessage()), OutputInterface::VERBOSITY_DEBUG);
            }

            $fileRemoved++;
            if ($fileRemoved % 100 === 0) {
                $progressBar->advance(100);
            }
        }

        $progressBar->finish();
    }

    private function removeFilesFromFileInfo()
    {
        $sqlQuery = <<<SQL
            DELETE file_info
            FROM `akeneo_file_storage_file_info` as file_info
            JOIN
                `akeneo_asset_manager_file_info_purge` as file_info_to_purge ON file_info_to_purge.file_key = file_info.file_key
            WHERE file_info.storage = 'assetStorage';
        SQL;

        $this->connection->executeUpdate(
            $sqlQuery
        );
    }

    private function removePurgeTable()
    {
        $sqlQuery = <<<SQL
            DROP TABLE IF EXISTS `akeneo_asset_manager_file_info_purge`;
        SQL;

        $this->connection->executeUpdate(
            $sqlQuery
        );
    }
}
