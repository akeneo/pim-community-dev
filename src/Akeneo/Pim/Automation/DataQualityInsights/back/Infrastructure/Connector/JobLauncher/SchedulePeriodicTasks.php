<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobLauncher;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters\PeriodicTasksParameters;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Bundle\BatchQueueBundle\Queue\JobExecutionMessageRepository;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Security\Core\User\User;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SchedulePeriodicTasks
{
    /** @var JobLauncherInterface */
    private $queueJobLauncher;

    /** @var JobInstanceRepository */
    private $jobInstanceRepository;

    /** @var JobExecutionMessageRepository */
    private $jobExecutionMessageRepository;

    public function __construct(
        JobLauncherInterface $queueJobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        JobExecutionMessageRepository $jobExecutionMessageRepository
    ) {
        $this->queueJobLauncher = $queueJobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobExecutionMessageRepository = $jobExecutionMessageRepository;
    }

    public function schedule(\DateTimeImmutable $date): void
    {
        $jobInstance = $this->getJobInstance();
        $user = new User(UserInterface::SYSTEM_USER_NAME, null);
        $jobParameters = [
            PeriodicTasksParameters::DATE_FIELD => $date->format(PeriodicTasksParameters::DATE_FORMAT),
        ];

        $this->queueJobLauncher->launch($jobInstance, $user, $jobParameters);
    }

    private function getJobInstance(): JobInstance
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('data_quality_insights_periodic_tasks');

        if (!$jobInstance instanceof JobInstance) {
            throw new \RuntimeException('The job instance "data_quality_insights_periodic_tasks" does not exist. Please contact your administrator.');
        }

        return $jobInstance;
    }
}
