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

namespace Akeneo\Platform\JobAutomation\Domain\Event;

use Akeneo\Platform\JobAutomation\Domain\Model\DueJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use Akeneo\Platform\JobAutomation\Domain\Model\UserToNotifyCollection;

/** @readonly */
class CouldNotLaunchAutomatedJobEvent
{
    private function __construct(
        public ScheduledJobInstance $scheduledJobInstance,
        public array $errorMessages,
        public UserToNotifyCollection $userToNotify,
    ) {
    }

    public static function dueToInvalidJobInstance(
        DueJobInstance $dueJobInstance,
        array $errorMessages,
    ): self {
        return new self($dueJobInstance->scheduledJobInstance, $errorMessages, $dueJobInstance->usersToNotify);
    }

    public static function dueToInternalError(
        DueJobInstance $dueJobInstance,
    ): self {
        return new self($dueJobInstance->scheduledJobInstance, ['Internal system failure'], $dueJobInstance->usersToNotify);
    }
}
