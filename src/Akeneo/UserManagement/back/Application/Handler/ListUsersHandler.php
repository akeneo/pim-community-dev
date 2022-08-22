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

use Akeneo\UserManagement\ServiceApi\User\ListUsersHandlerInterface;
use Akeneo\UserManagement\ServiceApi\UserGroup\ListUserGroupQuery;
use Akeneo\UserManagement\ServiceApi\UserGroup\UserGroup;
use Akeneo\UserManagement\Domain\Storage\FindUsers;
use Akeneo\UserManagement\ServiceApi\User\ListUsersQuery;

final class ListUsersHandler implements ListUsersHandlerInterface
{
    public function __construct(
        private FindUsers $findUsers
    ) {
    }

    public function fromQuery(ListUsersQuery $query): array
    {
        return ($this->findUsers)(
            $query->getSearchName(),
            $query->getSearchAfterId(),
            $query->getLimit(),
        );
    }
}
