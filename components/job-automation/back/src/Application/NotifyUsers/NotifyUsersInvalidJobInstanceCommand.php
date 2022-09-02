<?php

namespace Akeneo\Platform\JobAutomation\Application\NotifyUsers;

use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotifyCollection;

class NotifyUsersInvalidJobInstanceCommand
{
    public function __construct(
        public string $errorMessage,
        public ScheduledJobInstance $scheduledJobInstance,
        public UserToNotifyCollection $usersToNotify,
    ) {
    }
}
