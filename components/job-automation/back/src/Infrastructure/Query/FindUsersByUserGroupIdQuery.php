<?php

namespace Akeneo\Platform\JobAutomation\Infrastructure\Query;

use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotify;
use Akeneo\Platform\JobAutomation\Domain\Query\FindUsersByUserGroupIdQueryInterface;
use Akeneo\UserManagement\ServiceApi\User\ListUsersHandlerInterface;
use Akeneo\UserManagement\ServiceApi\User\User;
use Akeneo\UserManagement\ServiceApi\User\UsersQuery;

class FindUsersByUserGroupIdQuery implements FindUsersByUserGroupIdQueryInterface
{
    public function __construct(
        private readonly ListUsersHandlerInterface $listUsersHandler,
    ) {
    }

    public function execute(array $userGroupIds): array
    {
        $query = new UsersQuery(includeGroupIds: $userGroupIds);
        $users = $this->listUsersHandler->fromQuery($query);

        return array_map(
            static fn (User $user) => new UserToNotify($user->getUsername(), $user->getEmail()),
            $users,
        );
    }
}
