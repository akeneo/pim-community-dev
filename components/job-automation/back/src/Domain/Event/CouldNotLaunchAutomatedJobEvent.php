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
        ScheduledJobInstance $scheduledJobInstance,
        array $errorMessages,
        UserToNotifyCollection $userToNotify,
    ): self {
        return new self($scheduledJobInstance, $errorMessages, $userToNotify);
    }

    public static function dueToInternalError(
        ScheduledJobInstance $scheduledJobInstance,
        UserToNotifyCollection $userToNotify,
    ): self {
        return new self($scheduledJobInstance, ['Internal error'], $userToNotify);
    }
}
