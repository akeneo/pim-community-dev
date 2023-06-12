<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\ServiceApi\JobExecution;

use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionInterface;
use Akeneo\Platform\Job\Application\SearchJobExecution\SearchJobExecutionQuery;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

class FindQueuedAndRunningJobExecution implements FindQueuedAndRunningJobExecutionInterface
{
    private const QUEUED_AND_RUNNING_STATUS = ['STARTING', 'IN_PROGRESS'];

    public function __construct(
        private readonly SearchJobExecutionInterface $searchJobExecution,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function count(int $size = 25): int
    {
        $query = new SearchJobExecutionQuery();
        $query->size = $size;
        $query->status = self::QUEUED_AND_RUNNING_STATUS;

        return $this->searchJobExecution->count($query);
    }
}
