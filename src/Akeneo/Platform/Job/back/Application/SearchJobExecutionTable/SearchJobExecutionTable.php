<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Application\SearchJobExecutionTable;

use Akeneo\Platform\Job\Domain\Query\CountJobExecutionQueryInterface;
use Akeneo\Platform\Job\Domain\Query\FindJobExecutionRows\FindJobExecutionRowsForQueryInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class SearchJobExecutionTable
{
    private FindJobExecutionRowsForQueryInterface $findJobExecutionRowsForQuery;
    private CountJobExecutionQueryInterface $countJobExecutionQuery;

    public function __construct(
        FindJobExecutionRowsForQueryInterface $findJobExecutionRowsForQuery,
        CountJobExecutionQueryInterface $countJobExecutionQuery
    ) {
        $this->findJobExecutionRowsForQuery = $findJobExecutionRowsForQuery;
        $this->countJobExecutionQuery = $countJobExecutionQuery;
    }

    public function search(SearchExecutionTableQuery $query): SearchJobExecutionTableResult
    {
        $findJobExecutionRowsResult = $this->findJobExecutionRowsForQuery->find($query);
        $totalCount = $this->countJobExecutionQuery->all();

        return new SearchJobExecutionTableResult($findJobExecutionRowsResult->jobExecutionRows, $findJobExecutionRowsResult->matchesCount, $totalCount);
    }
}
