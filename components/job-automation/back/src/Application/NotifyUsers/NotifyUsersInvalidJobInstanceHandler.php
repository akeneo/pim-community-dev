<?php

namespace Akeneo\Platform\JobAutomation\Application\NotifyUsers;

use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotify;
use Akeneo\Platform\JobAutomation\Domain\Query\FindUsersByIdQueryInterface;
use Akeneo\Platform\JobAutomation\Domain\Query\FindUsersByUserGroupIdQueryInterface;
use Akeneo\Platform\JobAutomation\Domain\UserNotifierInterface;

class NotifyUsersInvalidJobInstanceHandler
{
    public function __construct(
        private FindUsersByIdQueryInterface $findUsersByIdQuery,
        private FindUsersByUserGroupIdQueryInterface $findUsersByUserGroupIdQuery,
        private UserNotifierInterface $userNotifier,
    ) {
    }

    public function handle(NotifyUsersInvalidJobInstanceCommand $command): void
    {
        $usersToNotify = [];

        if (!empty($command->notifiedUsers)) {
            $usersToNotify = $this->findUsersByIdQuery->execute($command->notifiedUsers);
        }

        if (!empty($command->notifiedUserGroups)) {
            $usersToNotify = \array_merge(
                $usersToNotify,
                $this->findUsersByUserGroupIdQuery->execute($command->notifiedUserGroups),
            );
        }

        if (empty($usersToNotify)) {
            return;
        }

        $usersToNotify = $this->arrayUniqueUsers($usersToNotify);

        $this->userNotifier->forInvalidJobInstance(
            $usersToNotify,
            $command->scheduledJobInstance,
            $command->errorMessage,
        );
    }

    /**
     * @param UserToNotify[] $users
     *
     * @return UserToNotify[]
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
