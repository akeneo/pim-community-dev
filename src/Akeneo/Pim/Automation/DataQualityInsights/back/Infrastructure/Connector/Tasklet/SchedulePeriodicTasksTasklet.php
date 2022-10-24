<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobLauncher\SchedulePeriodicTasks;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * Schedule the periodic tasks of Data-Quality-Insights.
 *
 * @author    Brice LE BOULC'H <brice.leboulch@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SchedulePeriodicTasksTasklet implements TaskletInterface
{
    protected const JOB_CODE = 'schedule_dqi_periodic_tasks';

    protected StepExecution $stepExecution;

    public function __construct(private SchedulePeriodicTasks $schedulePeriodicTasks, private FeatureFlag $featureFlag)
    {
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute(): void
    {
        if ($this->featureFlag->isEnabled()) {
            $this->schedulePeriodicTasks->schedule(new \DateTimeImmutable('-1 DAY'));
        }
    }
}
