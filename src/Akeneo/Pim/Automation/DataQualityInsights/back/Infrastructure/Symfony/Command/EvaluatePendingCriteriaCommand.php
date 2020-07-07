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

use Akeneo\Pim\Automation\DataQualityInsights\Application\FeatureFlag;
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

    /** @var string */
    private $projectDir;

    /** @var FeatureFlag */
    private $featureFlag;

    public function __construct(
        EntityManager $entityManager,
        JobExecutionManager $executionManager,
        JobRepositoryInterface $jobRepository,
        FeatureFlag $featureFlag,
        string $projectDir
    ) {
        parent::__construct();

        $this->executionManager = $executionManager;
        $this->jobRepository = $jobRepository;
        $this->entityManager = $entityManager;
        $this->featureFlag = $featureFlag;
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
        if (! $this->featureFlag->isEnabled()) {
            $output->writeln('Data Quality Insights feature is disabled');
            return;
        }

        $io = new SymfonyStyle($input, $output);
        $jobInstance = $this->getJobInstance();

        $this->ensureNoOtherJobExecutionIsRunning($jobInstance, $io);

        $jobExecution = $this->createJobExecution($jobInstance);
        $jobExecutionMessage = JobExecutionMessage::createJobExecutionMessage($jobExecution->getId(), []);
        $io->writeln(sprintf('Launching job execution "%s" to evaluate all pending criteria.', $jobExecution->getId()));

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
                $this->writeProcessOutput($process, $io);
            }
        } catch (\Throwable $e) {
            $io->error(sprintf('An error occurred: %s', $e->getMessage()));
            $io->error($e->getTraceAsString());
        }

        // update status if the job execution failed due to an uncatchable error as a fatal error
        $exitStatus = $this->executionManager->getExitStatus($jobExecutionMessage);
        if ($exitStatus->isRunning()) {
            $this->executionManager->markAsFailed($jobExecutionMessage);
        }

        $io->success(sprintf('Job execution "%s" is finished.', $jobExecutionMessage->getJobExecutionId()));
    }

    private function writeProcessOutput(Process $process, SymfonyStyle $io): void
    {
        $io->write($process->getIncrementalOutput());

        $errors = $process->getIncrementalErrorOutput();
        if ($errors) {
            $io->error($errors);
        }
    }

    private function ensureNoOtherJobExecutionIsRunning(JobInstance $jobInstance, SymfonyStyle $io): void
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

        $io->warning(sprintf('Another job execution is still running (id = "%d")', $jobExecutionRunning->getId()));

        // In case of an old job execution that has not been marked as failed.
        if ($jobExecutionRunning->getUpdatedTime() < new \DateTime(self::OUTDATED_JOB_EXECUTION_TIME)) {
            $io->writeln(sprintf('Job execution "%d" is outdated: let\'s mark it has failed.', $jobExecutionRunning->getId()));
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
