<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\MessageHandler;

use Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Component\Batch\Query\GetJobInstanceCode;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessageInterface;
use Psr\Log\LoggerInterface;
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

    private JobExecutionManager $executionManager;
    private LoggerInterface $logger;
    private string $projectDir;

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

    public function __invoke(JobExecutionMessageInterface $jobExecutionMessage)
    {
        $pathFinder = new PhpExecutableFinder();
        $console = sprintf('%s%sbin%sconsole', $this->projectDir, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);
        $startTime = time();
        try {
            $arguments = array_merge(
                [$pathFinder->find(), $console, 'akeneo:batch:watchdog', '--quiet'],
                $this->extractArgumentsFromMessage($jobExecutionMessage)
            );
            $tenantId = $jobExecutionMessage->tenantId;
            $env = [
                'APP_TENANT_ID' => $tenantId,
            ];
            if (preg_match('/^srnt-(.*)/', $tenantId, $matches)) {
                $env['APP_DATABASE_HOST'] = sprintf('pim-mysql-srnt-%s', $matches[1]);
                $env['APP_INDEX_HOSTS'] = sprintf('elasticsearch-client-srnt-%s', $matches[1]);
                $env['SYMFONY_DOTENV_VARS'] = '';
            }

            $process = new Process($arguments, null, $env);
            $process->setTimeout(null);

            $this->logger->notice('Start job execution loop', [
                'tenant_id' => $tenantId,
                'job_execution_id' => $jobExecutionMessage->getJobExecutionId(),
            ]);
            $this->logger->debug('Start job execution loop', [
                'tenant_id' => $tenantId,
                'job_execution_id' => $jobExecutionMessage->getJobExecutionId(),
                'command' => sprintf('Command line: "%s"', $process->getCommandLine()),
            ]);

            $process->run();
        } catch (\Throwable $t) {
            $this->logger->error('Job execution loop', [
                'exception_message' => $t->getMessage(),
                'trace' => $t->getTraceAsString(),
            ]);
        }

        $executionTimeInSec = time() - $startTime;
        $this->logger->notice('Job execution finished', [
            'job_execution_id' => $jobExecutionMessage->getJobExecutionId(),
            'execution_time_in_sec' => $executionTimeInSec,
        ]);
    }

    /**
     * Return all the arguments of the command to execute.
     * Options are considered as arguments.
     */
    private function extractArgumentsFromMessage(JobExecutionMessageInterface $jobExecutionMessage): array
    {
        $arguments = [
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
}
