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

use Akeneo\Platform\JobAutomation\Domain\Model\ScheduledJobInstance;
use Webmozart\Assert\Assert;

class FilterDueJobInstances
{
    public function __construct(
        private CronExpressionFactory $cronExpressionFactory,
    ) {
    }

    /**
     * @return ScheduledJobInstance[]
     */
    public function fromScheduledJobInstances(array $scheduledJobInstances): array
    {
        Assert::allIsInstanceOf($scheduledJobInstances, ScheduledJobInstance::class);

        return array_filter($scheduledJobInstances, function (ScheduledJobInstance $jobInstance) {
            $cron = $this->cronExpressionFactory->fromExpression($jobInstance->cronExpression);

            if (null === $jobInstance->lastExecutionDate) {
                $jobIsDue = $cron->isDue() || $cron->getPreviousRunDate() > $jobInstance->setupDate;
            } else {
                $jobIsDue = $cron->isDue() || $cron->getPreviousRunDate() > $jobInstance->lastExecutionDate;
            }

            return $jobIsDue;
        });
    }
}
