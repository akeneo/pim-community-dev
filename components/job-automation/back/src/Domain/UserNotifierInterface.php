<?php

namespace Akeneo\Platform\JobAutomation\Domain;

use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotify;

interface UserNotifierInterface
{
    /** @param UserToNotify[] $usersToNotify */
    public function forInvalidJobInstance(
        array $usersToNotify,
        ScheduledJobInstance $jobInstance,
        string $errorMessage,
    ): void;
}
