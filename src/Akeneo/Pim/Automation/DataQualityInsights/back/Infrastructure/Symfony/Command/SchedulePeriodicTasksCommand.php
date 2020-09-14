<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobLauncher\SchedulePeriodicTasks;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SchedulePeriodicTasksCommand extends Command
{
    /** @var SchedulePeriodicTasks */
    private $schedulePeriodicTasks;

    /** @var FeatureFlag */
    private $featureFlag;

    public function __construct(SchedulePeriodicTasks $schedulePeriodicTasks, FeatureFlag $featureFlag)
    {
        parent::__construct();

        $this->schedulePeriodicTasks = $schedulePeriodicTasks;
        $this->featureFlag = $featureFlag;
    }

    protected function configure()
    {
        $this
            ->setName('pim:data-quality-insights:schedule-periodic-tasks')
            ->setDescription('Schedule the periodic tasks of Data-Quality-Insights.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (! $this->featureFlag->isEnabled()) {
            $output->writeln('Data Quality Insights feature is disabled');
            return;
        }
        $this->schedulePeriodicTasks->schedule(new \DateTimeImmutable('-1 DAY'));

        $output->writeln('Data-Quality-Insights periodic tasks have been scheduled');
    }
}
