<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Infrastructure\Query;

use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotify;
use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotifyCollection;
use Akeneo\Platform\JobAutomation\Domain\Query\FindUsersByIdQueryInterface;
use Akeneo\Platform\JobAutomation\Domain\Query\FindUsersByUserGroupIdQueryInterface;
use Akeneo\Platform\JobAutomation\Domain\Query\FindUsersToNotifyQueryInterface;

final class FindUsersToNotifyQuery implements FindUsersToNotifyQueryInterface
{
    public function __construct(
        private readonly FindUsersByIdQueryInterface $findUsersByIdQuery,
        private readonly FindUsersByUserGroupIdQueryInterface $findUsersByUserGroupIdQuery,
    ) {
    }

    /**
     * @param array<int> $userIds
     * @param array<int> $userGroupIds
     */
    public function byUserIdsAndUserGroupsIds(array $userIds, array $userGroupIds): UserToNotifyCollection
    {
        $usersToNotify = [];

        if (!empty($userIds)) {
            $usersToNotify = $this->findUsersByIdQuery->execute($userIds);
        }

        if (!empty($userGroupIds)) {
            $usersToNotify = \array_merge(
                $usersToNotify,
                $this->findUsersByUserGroupIdQuery->execute($userGroupIds),
            );
        }

        if (!empty($usersToNotify)) {
            $usersToNotify = $this->arrayUniqueUsers($usersToNotify);
        }

        return new UserToNotifyCollection($usersToNotify);
    }

    /**
     * @param array<UserToNotify> $users
     *
     * @return array<UserToNotify>
     */
    private function arrayUniqueUsers(array $users): array
    {
        $usersIndexedByUsername = [];
        foreach ($users as $user) {
            $usersIndexedByUsername[$user->getUsername()] = $user;
        }

        return array_values($usersIndexedByUsername);
    }
}
