<?php

namespace Akeneo\Platform\Job\Domain\Query;

use Akeneo\Platform\Job\Application\SearchJobExecution\FindJobUsersQuery;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface FindJobUsersInterface
{
    /**
     * @return string[]
     */
    public function search(FindJobUsersQuery $query): array;
}
