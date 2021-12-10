<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Application\SearchJobUsers;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class SearchJobUsersHandler
{
    public function __construct(
        private SearchJobUsersInterface $searchJobUsers,
    ) {
    }

    public function search(SearchJobUsersQuery $query): array
    {
        return $this->searchJobUsers->search($query);
    }
}
