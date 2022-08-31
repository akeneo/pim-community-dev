<?php

namespace Akeneo\Platform\JobAutomation\Application\NotifyUsers;

use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;

class NotifyUsersInvalidJobInstanceCommand
{
    /**
     * @param int[] $notifiedUserGroups
     * @param int[] $notifiedUsers
     */
    public function __construct(
        public string $errorMessage,
        public ScheduledJobInstance $scheduledJobInstance,
        public array $notifiedUserGroups,
        public array $notifiedUsers,
    ) {
    }
}
