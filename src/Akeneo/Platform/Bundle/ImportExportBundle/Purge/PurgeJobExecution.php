<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Purge;

use Akeneo\Platform\Bundle\ImportExportBundle\Persistence\Filesystem\DeleteOrphanJobExecutionDirectories;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\DeleteJobExecution;
use Akeneo\Tool\Bundle\BatchBundle\Storage\DeleteJobExecutionLogs;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class PurgeJobExecution
{
    private DeleteJobExecution $deleteJobExecution;
    private DeleteOrphanJobExecutionDirectories $deleteOrphansJobExecutionDirectories;
    private DeleteJobExecutionLogs $deleteJobExecutionLogs;

    public function __construct(
        DeleteJobExecution $deleteJobExecution,
        DeleteOrphanJobExecutionDirectories $deleteOrphansJobExecutionDirectories,
        DeleteJobExecutionLogs $deleteJobExecutionLogs
    ) {
        $this->deleteJobExecution = $deleteJobExecution;
        $this->deleteOrphansJobExecutionDirectories = $deleteOrphansJobExecutionDirectories;
        $this->deleteJobExecutionLogs = $deleteJobExecutionLogs;
    }

    public function olderThanDays(int $days, array $jobInstanceCodes, ?BatchStatus $status): int
    {
        $this->deleteJobExecutionLogs->olderThanDays($days, $jobInstanceCodes, $status);
        $numberOfDeletedJobExecutions = $this->deleteJobExecution->olderThanDays($days, $jobInstanceCodes, $status);
        $this->deleteOrphansJobExecutionDirectories->execute();

        return $numberOfDeletedJobExecutions;
    }

    public function olderThanHours(int $hours, array $jobInstanceCodes, ?BatchStatus $status): int
    {
        $this->deleteJobExecutionLogs->olderThanHours($hours, $jobInstanceCodes, $status);
        $numberOfDeletedJobExecutions = $this->deleteJobExecution->olderThanHours($hours, $jobInstanceCodes, $status);
        $this->deleteOrphansJobExecutionDirectories->execute();

        return $numberOfDeletedJobExecutions;
    }

    public function all(array $jobInstanceCodes, ?BatchStatus $status): int
    {
        $this->deleteJobExecutionLogs->all($jobInstanceCodes, $status);
        $numberOfDeletedJobExecutions = $this->deleteJobExecution->all($jobInstanceCodes, $status);
        $this->deleteOrphansJobExecutionDirectories->execute();

        return $numberOfDeletedJobExecutions;
    }
}
