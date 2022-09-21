<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\JobAutomation\Domain\Model;

final class DueJobInstance
{
    public function __construct(
        private ScheduledJobInstance   $scheduledJobInstance,
        private UserToNotifyCollection $usersToNotify,
    )
    {
    }

    public function getScheduledJobInstance(): ScheduledJobInstance
    {
        return $this->scheduledJobInstance;
    }

    public function getUsersToNotify(): UserToNotifyCollection
    {
        return $this->usersToNotify;
    }
}
