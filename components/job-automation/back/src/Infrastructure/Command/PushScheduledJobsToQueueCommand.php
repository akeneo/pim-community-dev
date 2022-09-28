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

namespace Akeneo\Platform\JobAutomation\Infrastructure\Command;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\JobAutomation\Application\PushScheduledJobsToQueue\PushScheduledJobsToQueueHandlerInterface;
use Akeneo\Platform\JobAutomation\Application\PushScheduledJobsToQueue\PushScheduledJobsToQueueQuery;
use Akeneo\Platform\JobAutomation\Domain\Query\FindScheduledJobInstancesQueryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PushScheduledJobsToQueueCommand extends Command
{
    public static $defaultName = 'pim:job-automation:push-scheduled-jobs-to-queue';

    public function __construct(
        private FeatureFlag $jobAutomationFeatureFlag,
        private FindScheduledJobInstancesQueryInterface $findScheduledJobInstancesQuery,
        private PushScheduledJobsToQueueHandlerInterface $pushScheduledJobsToQueueHandler,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->jobAutomationFeatureFlag->isEnabled()) {
            return 0;
        }

        $this->pushScheduledJobsToQueueHandler->handle(
            new PushScheduledJobsToQueueQuery($this->findScheduledJobInstancesQuery->all()),
        );

        return 0;
    }
}
