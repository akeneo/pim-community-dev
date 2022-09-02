<?php

namespace Akeneo\Platform\JobAutomation\Application\NotifyUsers;

use Akeneo\Platform\JobAutomation\Domain\UserNotifierInterface;

class NotifyUsersInvalidJobInstanceHandler
{
    public function __construct(
        private UserNotifierInterface $userNotifier,
    ) {
    }

    public function handle(NotifyUsersInvalidJobInstanceCommand $command): void
    {
        $this->userNotifier->forInvalidJobInstance(
            $command->usersToNotify,
            $command->scheduledJobInstance,
            $command->errorMessage,
        );
    }
}
