<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Persistence\Filesystem;

use Doctrine\DBAL\Connection;
use League\Flysystem\DirectoryListing;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\StorageAttributes;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteOrphanJobExecutionDirectories
{
    private FilesystemOperator $archivistFilesystem;
    private Connection $connection;

    /** @var array<int, bool> */
    private array $jobExecutionIds = [];

    public function __construct(FilesystemOperator $archivistFilesystem, Connection $connection)
    {
        $this->archivistFilesystem = $archivistFilesystem;
        $this->connection = $connection;
    }

    public function execute(): void
    {
        $listDirs = fn (string $path): DirectoryListing => $this->archivistFilesystem
            ->listContents($path, false)
            ->filter(
                fn (StorageAttributes $attributes): bool => $attributes->isDir()
            );

        foreach ($listDirs('.') as $level1Directory) {
            foreach ($listDirs($level1Directory->path()) as $level2Directory) {
                foreach ($listDirs($level2Directory->path()) as $level3Directory) {
                    $jobExecutionId = $this->getJobExecutionIdFromPath($level3Directory->path());
                    if (null !== $jobExecutionId && false === $this->jobExecutionExists($jobExecutionId)) {
                        $this->archivistFilesystem->deleteDirectory($level3Directory->path());
                    }
                }
            }
        }
    }

    private function getJobExecutionIdFromPath(string $path): ?int
    {
        $dirNames = \explode(DIRECTORY_SEPARATOR, $path);
        if (!isset($dirNames[2]) || !\preg_match('/^\d+$/', $dirNames[2])) {
            return null;
        }

        return (int) $dirNames[2];
    }

    private function jobExecutionExists(int $jobExecutionId): bool
    {
        if (!isset($this->jobExecutionIds[$jobExecutionId])) {
            $sql = 'SELECT EXISTS(SELECT * FROM akeneo_batch_job_execution WHERE id = :job_execution_id)';

            $exists = $this->connection->executeQuery(
                $sql,
                ['job_execution_id' => $jobExecutionId]
            )->fetchOne();

            $this->jobExecutionIds[$jobExecutionId] = (bool) $exists;
        }

        return $this->jobExecutionIds[$jobExecutionId];
    }
}
