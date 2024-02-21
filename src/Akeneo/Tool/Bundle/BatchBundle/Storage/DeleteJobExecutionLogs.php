<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\Storage;

use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\GetJobExecutionIds;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Doctrine\DBAL\FetchMode;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteJobExecutionLogs
{
    public function __construct(
        private GetJobExecutionIds $getJobExecutionIds,
        private Filesystem $filesystem,
        private string $logDir
    ) {
    }

    public function olderThanDays(int $days, array $jobInstanceCodes, ?BatchStatus $jobExecutionStatus): void
    {
        $statement = $this->getJobExecutionIds->olderThanDays($days, $jobInstanceCodes, $jobExecutionStatus);
        while ($id = $statement->fetchOne()) {
            $this->filesystem->remove($this->getJobExecutionLogDirectory($id));
        }
    }

    public function olderThanHours(int $hours, array $jobInstanceCodes, ?BatchStatus $jobExecutionStatus): void
    {
        $statement = $this->getJobExecutionIds->olderThanHours($hours, $jobInstanceCodes, $jobExecutionStatus);
        while ($id = $statement->fetchOne()) {
            $this->filesystem->remove($this->getJobExecutionLogDirectory($id));
        }
    }

    public function all(array $jobInstanceCodes, ?BatchStatus $jobExecutionStatus): void
    {
        $statement = $this->getJobExecutionIds->all($jobInstanceCodes, $jobExecutionStatus);
        while ($id = $statement->fetchOne()) {
            $this->filesystem->remove($this->getJobExecutionLogDirectory($id));
        }
    }

    private function getJobExecutionLogDirectory(string $jobExecutionId): string
    {
        return sprintf('%s/%s', $this->logDir, $jobExecutionId);
    }
}
