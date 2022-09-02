<?php

namespace Akeneo\Platform\JobAutomation\Domain;

use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotifyCollection;

interface UserNotifierInterface
{
    public function forInvalidJobInstance(
        UserToNotifyCollection $usersToNotify,
        ScheduledJobInstance $jobInstance,
        string $errorMessage,
    ): void;
}
