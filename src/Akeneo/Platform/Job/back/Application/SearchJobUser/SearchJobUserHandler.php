<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Application\SearchJobUser;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class SearchJobUserHandler
{
    public function __construct(
        private SearchJobUserInterface $searchJobUser,
    ) {
    }

    public function search(SearchJobUserQuery $query): array
    {
        return $this->searchJobUser->search($query);
    }
}
