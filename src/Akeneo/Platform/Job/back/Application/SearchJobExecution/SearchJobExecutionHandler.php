<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Application\SearchJobExecution;

use Akeneo\Platform\Job\Domain\Query\CountJobExecutionQueryInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class SearchJobExecutionHandler
{
    private SearchJobExecutionInterface $findJobExecutionRowsForQuery;
    private CountJobExecutionQueryInterface $countJobExecutionQuery;

    public function __construct(
        SearchJobExecutionInterface $findJobExecutionRowsForQuery,
        CountJobExecutionQueryInterface $countJobExecutionQuery
    ) {
        $this->findJobExecutionRowsForQuery = $findJobExecutionRowsForQuery;
        $this->countJobExecutionQuery = $countJobExecutionQuery;
    }

    public function search(SearchJobExecutionQuery $query): JobExecutionTable
    {
        $jobExecutionRows = $this->findJobExecutionRowsForQuery->search($query);
        $matchesCount = $this->findJobExecutionRowsForQuery->count($query);
        $totalCount = $this->countJobExecutionQuery->all();

        return new JobExecutionTable($jobExecutionRows, $matchesCount, $totalCount);
    }
}
