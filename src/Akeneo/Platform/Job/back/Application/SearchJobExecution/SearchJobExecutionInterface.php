<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Application\SearchJobExecution;

use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionRow;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface SearchJobExecutionInterface
{
    /** @return JobExecutionRow[] */
    public function search(SearchJobExecutionQuery $query): array;

    public function count(SearchJobExecutionQuery $query): int;
}
