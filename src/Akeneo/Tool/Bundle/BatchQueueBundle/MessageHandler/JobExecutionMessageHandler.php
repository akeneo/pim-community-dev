<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\MessageHandler;

use Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Bundle\BatchQueueBundle\Queue\JobExecutionMessageRepository;
use Akeneo\Tool\Component\Batch\Query\GetJobInstanceCode;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessageInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class JobExecutionMessageHandler implements MessageHandlerInterface
{
    /** Interval in seconds before updating health check if job is still running. */
    public const HEALTH_CHECK_INTERVAL = 5;

    /** Interval in microseconds before checking if the process is still running. */
    private const RUNNING_PROCESS_CHECK_INTERVAL = 200000;

    private GetJobInstanceCode $getJobInstanceCode;
    private JobExecutionManager $executionManager;
    private LoggerInterface $logger;
    private string $projectDir;
    private ?UuidInterface $consumer = null;

    public function __construct(
        GetJobInstanceCode $getJobInstanceCode,
        JobExecutionManager $executionManager,
        LoggerInterface $logger,
        string $projectDir
    ) {
        $this->getJobInstanceCode = $getJobInstanceCode;
        $this->executionManager = $executionManager;
        $this->logger = $logger;
        $this->projectDir = $projectDir;
    }

    public function setConsumer(Uuid $consumer): void
    {
        $this->consumer = $consumer;
    }

    public function __invoke(JobExecutionMessageInterface $jobExecutionMessage)
    {
        if (!$this->consumer) {
            $this->consumer = Uuid::uuid4();
        }

        $this->logger->debug(sprintf('Consumer name: "%s"', $this->consumer));
        $jobExecutionMessage->consumedBy($this->consumer->toString());
        $pathFinder = new PhpExecutableFinder();
        $console = sprintf('%s%sbin%sconsole', $this->projectDir, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);

        $startTime = time();
        try {
            $arguments = array_merge(
                [$pathFinder->find(), $console, 'akeneo:batch:job'],
                $this->extractArgumentsFromMessage($jobExecutionMessage)
            );
            $process = new Process($arguments);
            $process->setTimeout(null);

            $this->logger->notice('Launching job execution "{job_execution_id}".', [
                'job_execution_id' => $jobExecutionMessage->getJobExecutionId(),
            ]);
            $this->logger->debug(sprintf('Command line: "%s"', $process->getCommandLine()));

            $this->executeProcess($process, $jobExecutionMessage);
        } catch (\Throwable $t) {
            $this->logger->error(sprintf('An error occurred: %s', $t->getMessage()));
            $this->logger->error($t->getTraceAsString());
        } finally {
            // update status if the job execution failed due to an uncatchable error as a fatal error
            $exitStatus = $this->executionManager->getExitStatus($jobExecutionMessage);
            if ($exitStatus && $exitStatus->isRunning()) {
                $this->executionManager->markAsFailed($jobExecutionMessage->getJobExecutionId());
            }
        }

        $executionTimeInSec = time() - $startTime;
        $this->logger->notice('Job execution "{job_execution_id}" is finished in {execution_time_in_sec} seconds.', [
            'job_execution_id' => $jobExecutionMessage->getJobExecutionId(),
            'execution_time_in_sec' => $executionTimeInSec,
        ]);
    }

    private function executeProcess(Process $process, JobExecutionMessageInterface $jobExecutionMessage): void
    {
        $this->executionManager->updateHealthCheck($jobExecutionMessage);
        $process->start();

        $nbIterationBeforeUpdatingHealthCheck = self::HEALTH_CHECK_INTERVAL * 1000000 / self::RUNNING_PROCESS_CHECK_INTERVAL;
        $iteration = 1;
        while ($process->isRunning()) {
            if ($iteration < $nbIterationBeforeUpdatingHealthCheck) {
                $iteration++;
                usleep(self::RUNNING_PROCESS_CHECK_INTERVAL);

                continue;
            }

            $this->writeProcessOutput($process);
            $this->executionManager->updateHealthCheck($jobExecutionMessage);
            $iteration = 1;
        }

        $this->writeProcessOutput($process);
    }

    /**
     * Return all the arguments of the command to execute.
     * Options are considered as arguments.
     */
    private function extractArgumentsFromMessage(JobExecutionMessageInterface $jobExecutionMessage): array
    {
        $jobInstanceCode = $this->getJobInstanceCode->fromJobExecutionId($jobExecutionMessage->getJobExecutionId());

        $arguments = [
            $jobInstanceCode,
            $jobExecutionMessage->getJobExecutionId(),
        ];

        foreach ($jobExecutionMessage->getOptions() as $optionName => $optionValue) {
            if (true === $optionValue) {
                $arguments[] = sprintf('--%s', $optionName);
            } elseif (false !== $optionValue) {
                $arguments[] = sprintf('--%s=%s', $optionName, $optionValue);
            }
        }

        return $arguments;
    }

    private function writeProcessOutput(Process $process): void
    {
        $errors = $process->getIncrementalErrorOutput();
        if ($errors) {
            $this->logger->error($errors);
        }
    }
}
