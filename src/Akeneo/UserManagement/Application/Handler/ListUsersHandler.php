<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Community Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\UserManagement\Application\Handler;

use Akeneo\UserManagement\API\User\ListUsersQuery;
use Akeneo\UserManagement\API\UserGroup\ListUserGroupQuery;
use Akeneo\UserManagement\API\UserGroup\UserGroup;
use Akeneo\UserManagement\Domain\Model\User as DomainUser;
use Akeneo\UserManagement\Domain\Storage\FindUsers;

final class ListUsersHandler
{
    public function __construct(
        private FindUsers $findUsers
    ) {
    }

    public function __invoke(ListUsersQuery $query): array
    {
        return ($this->findUsers)(
            $query->getSearchName(),
            $query->getLimit(),
            $query->getOffset()
        );
    }
}
