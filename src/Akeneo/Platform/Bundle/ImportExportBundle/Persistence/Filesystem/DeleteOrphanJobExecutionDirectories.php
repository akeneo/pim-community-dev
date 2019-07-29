<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Persistence\Filesystem;

use Doctrine\DBAL\Connection;
use League\Flysystem\Filesystem;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteOrphanJobExecutionDirectories
{
    /** @var Filesystem */
    private $archivistFilesystem;

    /** @var Connection */
    private $connection;

    public function __construct(Filesystem $archivistFilesystem, Connection $connection)
    {
        $this->archivistFilesystem = $archivistFilesystem;
        $this->connection = $connection;
    }

    public function execute(): void
    {
        $paths = $this->archivistFilesystem->listFiles('.', true);

        $jobExecutionIds = $this->getJobExecutionIdsFromPaths($paths);
        $existingJobExecutionIds = $this->getExistingJobExecutionIds($jobExecutionIds);
        $this->deleteOrphanJobExecutionDirectories($paths, $existingJobExecutionIds);
    }

    private function getJobExecutionIdsFromPaths(array $paths): array
    {
        $jobExecutionIds = [];
        foreach ($paths as $path) {
            $directories = explode(DIRECTORY_SEPARATOR, $path['dirname']);
            if (!isset($directories[2])) {
                continue;
            }

            $jobExecutionIds[] = $directories[2];
        }

        return $jobExecutionIds;
    }

    private function getExistingJobExecutionIds(array $jobExecutionIds): array
    {
        $sql = 'SELECT id FROM akeneo_batch_job_execution WHERE id IN (:job_execution_ids)';

        $existingJobExecutionIds = $this->connection->executeQuery(
            $sql,
            ['job_execution_ids' => $jobExecutionIds],
            ['job_execution_ids' => Connection::PARAM_STR_ARRAY]
        )->fetchAll(\PDO::FETCH_COLUMN);

        return $existingJobExecutionIds;
    }

    private function deleteOrphanJobExecutionDirectories(array $paths, array $existingJobExecutionIds): void
    {
        foreach ($paths as $path) {
            $directories = explode(DIRECTORY_SEPARATOR, $path['dirname']);
            if (!isset($directories[2])) {
                continue;
            }

            [$jobExecutionType, $jobName, $jobExecutionId] = explode(DIRECTORY_SEPARATOR, $path['dirname']);
            $pathToDelete = $jobExecutionType . DIRECTORY_SEPARATOR . $jobName . DIRECTORY_SEPARATOR . $jobExecutionId;

            if (!in_array($jobExecutionId, $existingJobExecutionIds) && $this->archivistFilesystem->has($pathToDelete)) {
                $this->archivistFilesystem->deleteDir($pathToDelete);
            }
        }
    }
}
