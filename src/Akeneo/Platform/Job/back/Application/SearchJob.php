<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Application;

use Akeneo\Platform\Job\Domain\Model\SearchJobResult;
use Akeneo\Platform\Job\Domain\Query\CountJobQueryInterface;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class SearchJob
{
    private CountJobQueryInterface $countJobQuery;

    public function __construct(CountJobQueryInterface $countJobQuery)
    {
        $this->countJobQuery = $countJobQuery;
    }

    public function search(): SearchJobResult
    {
        $totalCount = $this->countJobQuery->all();

        return new SearchJobResult([], 0, $totalCount);
    }
}
