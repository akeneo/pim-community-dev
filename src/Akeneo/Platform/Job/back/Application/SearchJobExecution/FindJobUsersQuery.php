<?php

namespace Akeneo\Platform\Job\Application\SearchJobExecution;

/**
 * Maybe this is just another read usecase ? How about ?
 * - We move this object in Application/SearchUsers/SearchUsersQuery
 * - Add the Akeneo\Platform\Job\Application\SearchJobExecution\FindJobUsersInterface in there
 *
 * OR even create new Application/SearchUser/SearchUsers/SearchUsersHandler => that calls the query FindJobUsersInterface
 *
 * I think we need to differenciate the 2 usecases: search jobs and search user jobs.
 *
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FindJobUsersQuery
{
    public string $search = '';
}
