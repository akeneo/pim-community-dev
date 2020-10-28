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
    private const BATCH_SIZE = 100;

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
        $pathsByBatch = $this->getPathsAtLevel3ByBatch();

        foreach ($pathsByBatch as $paths) {
            $jobExecutionIds = $this->getJobExecutionIdsFromPaths($paths);
            $existingJobExecutionIds = $this->getExistingJobExecutionIds($jobExecutionIds);
            $this->deleteOrphanJobExecutionDirectories($paths, $existingJobExecutionIds);
        }
    }

    /**
     * With flysystem v2 we have a memory leak when we fetch all files/paths recursively: the listContents
     * method gathers all file/directory data in one array. An out of memory error occurred when there is too many
     * files/directories. With flysystem v2 the problem should be resolved as the function uses a generator instead.
     * To try to fix this error with flysystem v2, we list the paths step by step to prevent memory errors.
     * We stop the listing at level 3 as for our use case we don't need to go deeper: the level 3
     * contains the id of the jobs that we are looking for.
     *
     * @TIP-1536: when flysystem v2 will be out, try to replace this method
     * by $this->archivistFilesystem->listPaths('.', true);
     *
     * @return \Iterator
     */
    private function getPathsAtLevel3ByBatch(): \Iterator
    {
        $paths = [];
        $directoryFilterFunction = function (array $content) {
            return $content['type'] === 'dir';
        };

        $firstLevelContents = array_filter(
            $this->archivistFilesystem->listContents('.', false),
            $directoryFilterFunction
        );
        foreach ($firstLevelContents as $firstLevelContent) {
            $secondLevelContents = array_filter(
                $this->archivistFilesystem->listContents($firstLevelContent['path'], false),
                $directoryFilterFunction
            );
            foreach ($secondLevelContents as $secondLevelContent) {
                $thirdLevelPaths = array_filter(
                    $this->archivistFilesystem->listContents($secondLevelContent['path'], false),
                    $directoryFilterFunction
                );
                foreach ($thirdLevelPaths as $thirdLevelPath) {
                    $paths[] = $thirdLevelPath['path'];
                    if (count($paths) >= self::BATCH_SIZE) {
                        yield $paths;
                        $paths = [];
                    }
                }
            }
        }

        if (count($paths) > 0) {
            yield $paths;
        }
    }

    private function getJobExecutionIdsFromPaths(array $paths): array
    {
        $jobExecutionIds = [];
        foreach ($paths as $path) {
            $directories = explode(DIRECTORY_SEPARATOR, $path);
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
            $directories = explode(DIRECTORY_SEPARATOR, $path);
            if (!isset($directories[2])) {
                continue;
            }

            [$jobExecutionType, $jobName, $jobExecutionId] = explode(DIRECTORY_SEPARATOR, $path);
            $pathToDelete = $jobExecutionType . DIRECTORY_SEPARATOR . $jobName . DIRECTORY_SEPARATOR . $jobExecutionId;

            if (!in_array($jobExecutionId, $existingJobExecutionIds) && $this->archivistFilesystem->has($pathToDelete)) {
                $this->archivistFilesystem->deleteDir($pathToDelete);
            }
        }
    }
}
