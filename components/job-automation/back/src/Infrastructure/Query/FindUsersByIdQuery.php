<?php

namespace Akeneo\Platform\JobAutomation\Infrastructure\Query;

use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotify;
use Akeneo\Platform\JobAutomation\Domain\Query\FindUsersByIdQueryInterface;
use Akeneo\UserManagement\ServiceApi\User\ListUsersHandlerInterface;
use Akeneo\UserManagement\ServiceApi\User\User;
use Akeneo\UserManagement\ServiceApi\User\UsersQuery;

class FindUsersByIdQuery implements FindUsersByIdQueryInterface
{
    public function __construct(
        private readonly ListUsersHandlerInterface $listUsersHandler,
    ) {
    }

    public function execute(array $ids): array
    {
        $query = new UsersQuery(includeIds: $ids);
        $users = $this->listUsersHandler->fromQuery($query);

        return array_map(
            static fn (User $user) => new UserToNotify($user->getUsername(), $user->getEmail()),
            $users,
        );
    }
}
