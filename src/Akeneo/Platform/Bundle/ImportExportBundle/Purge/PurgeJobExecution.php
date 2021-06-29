<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Purge;

use Akeneo\Platform\Bundle\ImportExportBundle\Persistence\Filesystem\DeleteOrphanJobExecutionDirectories;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\DeleteJobExecution;
use Akeneo\Tool\Bundle\BatchBundle\Storage\DeleteJobExecutionLogs;

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

    public function olderThanDays(int $days): int
    {
        $this->deleteJobExecutionLogs->olderThanDays($days);
        $numberOfDeletedJobExecutions = $this->deleteJobExecution->olderThanDays($days);
        $this->deleteOrphansJobExecutionDirectories->execute();

        return $numberOfDeletedJobExecutions;
    }

    public function all(): void
    {
        $this->deleteJobExecutionLogs->all();
        $this->deleteJobExecution->all();
        $this->deleteOrphansJobExecutionDirectories->execute();
    }
}
