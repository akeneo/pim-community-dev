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

use Akeneo\UserManagement\Domain\Model\User as DomainUser;
use Akeneo\UserManagement\Domain\Storage\FindUsers;
use Akeneo\UserManagement\ServiceApi\User\ListUsersHandlerInterface;
use Akeneo\UserManagement\ServiceApi\User\User as ServiceApiUser;
use Akeneo\UserManagement\ServiceApi\User\UsersQuery;

final class ListUsersHandler implements ListUsersHandlerInterface
{
    public function __construct(
        private FindUsers $findUsers
    ) {
    }

    public function fromQuery(UsersQuery $query): array
    {
        $result = ($this->findUsers)(
            $query->getSearch(),
            $query->getSearchAfterId(),
            $query->getIncludeIds(),
            $query->getIncludeGroupIds(),
            $query->getLimit(),
        );

        return array_map(
            static fn (DomainUser $user) => new ServiceApiUser(
                $user->getId(),
                $user->getEmail(),
                $user->getUsername(),
                $user->getUserType(),
                $user->getFirstname(),
                $user->getLastname(),
                $user->getMiddleName(),
                $user->getNameSuffix(),
                $user->getImage()
            ),
            $result
        );
    }
}
