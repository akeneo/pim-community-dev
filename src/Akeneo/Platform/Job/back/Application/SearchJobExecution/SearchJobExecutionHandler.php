<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Application\SearchJobExecution;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class SearchJobExecutionHandler
{
    private SearchJobExecutionInterface $findJobExecutionRowsForQuery;

    public function __construct(
        SearchJobExecutionInterface $findJobExecutionRowsForQuery
    ) {
        $this->findJobExecutionRowsForQuery = $findJobExecutionRowsForQuery;
    }

    public function search(SearchJobExecutionQuery $query): JobExecutionTable
    {
        $jobExecutionRows = $this->findJobExecutionRowsForQuery->search($query);
        $matchesCount = $this->findJobExecutionRowsForQuery->count($query);

        return new JobExecutionTable($jobExecutionRows, $matchesCount);
    }
}
