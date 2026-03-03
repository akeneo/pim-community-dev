<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Application\SearchJobExecution;

use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionTable;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class SearchJobExecutionHandler
{
    public function __construct(
        private SearchJobExecutionInterface $findJobExecutionRowsForQuery,
    ) {
    }

    public function search(SearchJobExecutionQuery $query): JobExecutionTable
    {
        $this->validateQuery($query);

        $jobExecutionRows = $this->findJobExecutionRowsForQuery->search($query);
        $matchesCount = $this->findJobExecutionRowsForQuery->count($query);

        return new JobExecutionTable($jobExecutionRows, $matchesCount);
    }

    private function validateQuery(SearchJobExecutionQuery $query): void
    {
        if (!in_array($query->sortColumn, SearchJobExecutionQuery::$supportedSortColumns)) {
            throw new \InvalidArgumentException(sprintf('Sort column "%s" is not supported', $query->sortColumn));
        }

        if (!in_array($query->sortDirection, SearchJobExecutionQuery::$supportedSortDirections)) {
            throw new \InvalidArgumentException(sprintf('Sort direction "%s" is not supported', $query->sortDirection));
        }

        if (SearchJobExecutionQuery::MAX_PAGE_WITHOUT_FILTER < $query->page) {
            throw new \InvalidArgumentException('Page can not be greater than 50 when no filter are set');
        }
    }
}
