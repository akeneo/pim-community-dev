<?php

declare(strict_types=1);

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            return 0;
        }
        $this->schedulePeriodicTasks->schedule(new \DateTimeImmutable('-1 DAY'));

        $output->writeln('Data-Quality-Insights periodic tasks have been scheduled');

        return 0;
    }
}
