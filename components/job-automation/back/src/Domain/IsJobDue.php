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

namespace Akeneo\Platform\JobAutomation\Domain;

use Akeneo\Platform\JobAutomation\Domain\Model\CronExpression;
use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use Webmozart\Assert\Assert;

class IsJobDue
{
    public static function fromScheduledJobInstance(ScheduledJobInstance $scheduledJobInstance, CronExpression $cronExpression): bool
    {
        Assert::isInstanceOf($scheduledJobInstance, ScheduledJobInstance::class);

        if (null === $scheduledJobInstance->lastExecutionDate) {
            return $cronExpression->isDue() || $cronExpression->getPreviousRunDate() > $scheduledJobInstance->setupDate;
        } else {
            return $cronExpression->isDue() || $cronExpression->getPreviousRunDate() > $scheduledJobInstance->lastExecutionDate;
        }
    }
}
