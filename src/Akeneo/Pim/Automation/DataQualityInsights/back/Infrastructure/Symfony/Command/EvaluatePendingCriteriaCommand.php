<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet\EvaluateProductsCriteriaTasklet;
use Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessage;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class EvaluatePendingCriteriaCommand extends Command
{
    /** Interval in seconds before checking if the process is still running. */
    private const RUNNING_PROCESS_CHECK_INTERVAL = 5;

    /** Time for which a job execution is considered as outdated. */
    private const OUTDATED_JOB_EXECUTION_TIME = '-1 DAY';

    /** @var JobExecutionManager */
    private $executionManager;

    /** @var JobRepositoryInterface */
    private $jobRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $projectDir;

    public function __construct(
        EntityManager $entityManager,
        JobExecutionManager $executionManager,
        JobRepositoryInterface $jobRepository,
        LoggerInterface $logger,
        string $projectDir
    ) {
        parent::__construct();

        $this->executionManager = $executionManager;
        $this->jobRepository = $jobRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->projectDir = $projectDir;
    }

    protected function configure()
    {
        $this
            ->setName('pimee:data-quality-insights:evaluate-products')
            ->setDescription('Launch the evaluation of all pending criteria');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $jobInstance = $this->getJobInstance();

        $this->ensureNoOtherJobExecutionIsRunning($jobInstance);

        $jobExecution = $this->createJobExecution($jobInstance);
        $jobExecutionMessage = JobExecutionMessage::createJobExecutionMessage($jobExecution->getId(), []);
        $this->logger->info('Launching job execution "{job_id}" to evaluate all pending criteria.', ['message' => 'start_evaluation_of_pending_criteria', 'job_id' => $jobExecution->getId()]);

        $pathFinder = new PhpExecutableFinder();
        $console = sprintf('%s%sbin%sconsole', $this->projectDir, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);

        try {
            $process = new Process([
                $pathFinder->find(),
                $console,
                'akeneo:batch:job',
                $jobInstance->getCode(),
                $jobExecution->getId()
            ]);
            $process->setTimeout(null);

            $this->executionManager->updateHealthCheck($jobExecutionMessage);

            $process->start();

            while ($process->isRunning()) {
                sleep(self::RUNNING_PROCESS_CHECK_INTERVAL);
                $this->executionManager->updateHealthCheck($jobExecutionMessage);
                $this->writeProcessOutput($process);
            }
        } catch (\Throwable $e) {
            $this->logger->error('An error occurred: {error_message}', ['error_message' => $e->getMessage(), 'error_trace' => $e->getTraceAsString()]);
        }

        // update status if the job execution failed due to an uncatchable error as a fatal error
        $exitStatus = $this->executionManager->getExitStatus($jobExecutionMessage);
        if ($exitStatus->isRunning()) {
            $this->executionManager->markAsFailed($jobExecutionMessage);
        }

        $this->logger->info('Job execution "{job_id}" is finished.', ['message' => 'job_execution_finished', 'job_id' => $jobExecutionMessage->getJobExecutionId()]);
    }

    private function writeProcessOutput(Process $process): void
    {
        $this->logger->info($process->getIncrementalOutput());

        $errors = $process->getIncrementalErrorOutput();
        if ($errors) {
            $this->logger->error($errors);
        }
    }

    private function ensureNoOtherJobExecutionIsRunning(JobInstance $jobInstance): void
    {
        $jobExecutionRunning = $this->entityManager
            ->getRepository(JobExecution::class)
            ->findOneBy([
                'jobInstance' => $jobInstance->getId(),
                'exitCode' => [ExitStatus::EXECUTING, ExitStatus::UNKNOWN]
            ]);

        if (null === $jobExecutionRunning) {
            return;
        }

        $this->logger->warning('Another job execution is still running (id = {job_id})', ['message' => 'another_job_execution_is_still_running', 'job_id' => $jobExecutionRunning->getId()]);

        // In case of an old job execution that has not been marked as failed.
        if ($jobExecutionRunning->getUpdatedTime() < new \DateTime(self::OUTDATED_JOB_EXECUTION_TIME)) {
            $this->logger->info('Job execution "{job_id}" is outdated: let\'s mark it has failed.', ['message' => 'job_execution_outdated', 'job_id' => $jobExecutionRunning->getId()]);
            $jobExecutionMessage = JobExecutionMessage::createJobExecutionMessage($jobExecutionRunning->getId(), []);
            $this->executionManager->markAsFailed($jobExecutionMessage);
        }

        exit(0);
    }

    private function getJobInstance(): JobInstance
    {
        $jobInstance = $this->entityManager
            ->getRepository(JobInstance::class)
            ->findOneBy(['code' => EvaluateProductsCriteriaTasklet::JOB_INSTANCE_NAME]);

        if (null === $jobInstance) {
            throw new \Exception(sprintf('Job "%s" not found', EvaluateProductsCriteriaTasklet::JOB_INSTANCE_NAME));
        }

        return $jobInstance;
    }

    private function createJobExecution(JobInstance $jobInstance): JobExecution
    {
        $jobExecution = $this->jobRepository->createJobExecution($jobInstance, new JobParameters([]));

        $jobExecution->setUser(UserInterface::SYSTEM_USER_NAME);
        $this->jobRepository->updateJobExecution($jobExecution);

        return $jobExecution;
    }
}
