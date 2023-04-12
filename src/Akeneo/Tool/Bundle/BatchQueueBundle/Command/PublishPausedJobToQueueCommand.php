<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Tool\Bundle\BatchQueueBundle\Command;

use Akeneo\Tool\Component\Batch\Job\JobParametersFactory;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\BatchQueue\Factory\JobExecutionMessageFactory;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\PausedJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueueInterface;
use Ramsey\Uuid\Rfc4122\UuidV4;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class PublishPausedJobToQueueCommand extends Command
{
    protected static $defaultName = 'akeneo:batch:publish-paused-job-to-queue';

    public function __construct(
        private readonly JobExecutionQueueInterface $jobExecutionQueue,
        private readonly JobRepositoryInterface $jobRepository,
        private readonly JobExecutionMessageFactory $jobExecutionMessageFactory,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $pausedJobIds = $this->jobRepository->getPausedJobExecutionIds();

            if (empty($pausedJobIds)) {
                return Command::SUCCESS;
            }

            foreach ($pausedJobIds as $pausedJobId) {
                $message = $this->jobExecutionMessageFactory->buildFromNormalized([
                    'id' => UuidV4::uuid4()->toString(),
                    'job_execution_id' => (int) $pausedJobId['id'],
                    'created_time' => null,
                    'updated_time' => null,
                    'options' => []
                ], PausedJobExecutionMessage::class);

                $this->jobExecutionQueue->publish($message);
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->write($e->getMessage());
            return Command::FAILURE;
        }
    }
}
