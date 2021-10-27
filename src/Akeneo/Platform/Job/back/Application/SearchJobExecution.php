<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Application;

use Akeneo\Platform\Job\Domain\Query\CountJobExecutionQueryInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class SearchJobExecution
{
    private CountJobExecutionQueryInterface $countJobExecutionQuery;

    public function __construct(CountJobExecutionQueryInterface $countJobExecutionQuery)
    {
        $this->countJobExecutionQuery = $countJobExecutionQuery;
    }

    public function search(): SearchJobExecutionTableResult
    {
        $totalCount = $this->countJobExecutionQuery->all();

        return new SearchJobExecutionTableResult([], $totalCount, $totalCount);
    }
}
