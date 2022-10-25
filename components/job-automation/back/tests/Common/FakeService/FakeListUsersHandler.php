<?php

namespace Akeneo\Platform\JobAutomation\Test\Common\FakeService;

use Akeneo\UserManagement\ServiceApi\User\ListUsersHandlerInterface;
use Akeneo\UserManagement\ServiceApi\User\User;
use Akeneo\UserManagement\ServiceApi\User\UsersQuery;

class FakeListUsersHandler implements ListUsersHandlerInterface
{
    /** @var User[] */
    private array $users;

    public function __construct()
    {
        $this->users = [
            new User(1, 'julia@example.com', 'julia', '', null, null, null, null, null),
            new User(2, 'peter@example.com', 'peter', '', null, null, null, null, null),
            new User(3, 'michel@example.com', 'michel', '', null, null, null, null, null),
            new User(4, 'adrien@example.com', 'adrien', '', null, null, null, null, null),
        ];
    }

    public function fromQuery(UsersQuery $query): array
    {
        $matchingUsers = $this->users;

        if (!empty($query->getIncludeIds())) {
            $matchingUsers = array_filter($this->users, static fn (User $user) => in_array($user->getId(), $query->getIncludeIds()));
        }

        if (!empty($query->getIncludeGroupIds())) {
            $matchingUsers = array_filter($this->users, static fn (User $user) => self::isUserInGroups($user, $query->getIncludeGroupIds()));
        }

        return $matchingUsers;
    }

    private static function isUserInGroups(User $user, array $includeGroupIds): bool
    {
        $userGroups = [
            'julia' => [1, 2, 3, 7],
            'peter' => [1, 5, 7],
            'michel' => [1, 7],
            'adrien' => [7],
        ];

        return !empty(array_intersect($includeGroupIds, $userGroups[$user->getUsername()]));
    }
}
