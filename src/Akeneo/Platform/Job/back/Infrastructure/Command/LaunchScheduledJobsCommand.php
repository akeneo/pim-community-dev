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

namespace Akeneo\Platform\Job\Infrastructure\Command;

use Akeneo\Platform\Job\Application\Schedule\GetDueJobs;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchQueueBundle\Launcher\QueueJobLauncher;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LaunchScheduledJobsCommand extends Command
{
    protected static $defaultName = 'akeneo:batch:launch-scheduled-jobs';

    public function __construct(
        private JobInstanceRepository $jobRepository,
        private UserRepositoryInterface $userRepository,
        private QueueJobLauncher $queueJobLauncher,
        private GetDueJobs $getDueJobs,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $admin = $this->userRepository->findOneByIdentifier('admin');

        $dueJobCodes = $this->getDueJobs->getDueJobs();
        $jobs = $this->jobRepository->findBy(['code' => $dueJobCodes]);

        foreach ($jobs as $job) {
            $this->queueJobLauncher->launch($job, $admin);
        }

        return 0;
    }
}
