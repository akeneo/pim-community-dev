<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\Command;

use Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Bundle\BatchQueueBundle\Queue\JobExecutionMessageRepository;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * This command is a daemon to consume job execution messages and launch the associated job execution in background.
 * A command can only execute one single job at a time.
 * The command will not launch any other jobs until the current job is finished.
 *
 * If you want to execute several jobs in parallel, you have to run several daemons.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobQueueConsumerCommand extends Command
{
    public const COMMAND_NAME = 'akeneo:batch:job-queue-consumer-daemon';

    protected static $defaultName = self::COMMAND_NAME;

    /** Interval in seconds before updating health check if job is still running. */
    public const HEALTH_CHECK_INTERVAL = 5;

    /** Interval in seconds to wait after an exception occurred.*/
    private const EXCEPTION_WAIT_INTERVAL = 5;

    /** Interval in microseconds before checking if the process is still running. */
    private const RUNNING_PROCESS_CHECK_INTERVAL = 200000;

    /** @var JobExecutionQueueInterface */
    private $jobExecutionQueue;

    /** @var JobExecutionMessageRepository */
    private $executionMessageRepository;

    /** @var JobExecutionManager */
    private $executionManager;

    /** @var string */
    private $projectDir;

    public function __construct(
        JobExecutionQueueInterface $jobExecutionQueue,
        JobExecutionMessageRepository $executionMessageRepository,
        JobExecutionManager $executionManager,
        string $projectDir
    ) {
        parent::__construct();

        $this->jobExecutionQueue = $jobExecutionQueue;
        $this->executionMessageRepository = $executionMessageRepository;
        $this->executionManager = $executionManager;
        $this->projectDir = $projectDir;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Launch a daemon that will consume job execution messages and launch the associated job execution in backgrounds')
            ->addOption('run-once', null, InputOption::VALUE_NONE, 'Launch only one job execution and stop the daemon once the job execution is finished')
            ->addOption('job', 'j', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Job instance codes that should be consumed')
            ->addOption('blacklisted-job', 'b', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Job instance codes that should not be consumed')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $whitelistedJobInstanceCodes = $input->getOption('job');
        $blacklistedJobInstanceCodes = $input->getOption('blacklisted-job');

        $errOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;

        $consumerName = Uuid::uuid4();
        $output->writeln(sprintf('Consumer name: "%s"', $consumerName->toString()));

        $pathFinder = new PhpExecutableFinder();
        $console = sprintf('%s%sbin%sconsole', $this->projectDir, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);

        do {
            try {
                $jobExecutionMessage = $this->jobExecutionQueue->consume($consumerName->toString(), $whitelistedJobInstanceCodes, $blacklistedJobInstanceCodes);

                $arguments = array_merge([$pathFinder->find(), $console, 'akeneo:batch:job' ], $this->getArguments($jobExecutionMessage));
                $process = new Process($arguments);

                $process->setTimeout(null);

                $output->writeln(sprintf('Launching job execution "%s".', $jobExecutionMessage->getJobExecutionId()));
                $output->writeln(sprintf('Command line: "%s"', $process->getCommandLine()));

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

                    $output->write($process->getIncrementalOutput());
                    $errOutput->write($process->getIncrementalErrorOutput());

                    $this->executionManager->updateHealthCheck($jobExecutionMessage);
                    $iteration = 1;
                }

                // update status if the job execution failed due to an uncatchable error as a fatal error
                $exitStatus = $this->executionManager->getExitStatus($jobExecutionMessage);
                if ($exitStatus->isRunning()) {
                    $this->executionManager->markAsFailed($jobExecutionMessage);
                }

                $output->write($process->getIncrementalOutput());
                $errOutput->write($process->getIncrementalErrorOutput());

                $output->writeln(sprintf('Job execution "%s" is finished.', $jobExecutionMessage->getJobExecutionId()));
            } catch (\Throwable $t) {
                $errOutput->writeln(sprintf('An error occurred: %s', $t->getMessage()));
                $errOutput->writeln($t->getTraceAsString());

                sleep(self::EXCEPTION_WAIT_INTERVAL);
            }
        } while (false === $input->getOption('run-once'));
    }

    /**
     * Return all the arguments of the command to execute.
     * Options are considered as arguments.
     *
     * @param JobExecutionMessage $jobExecutionMessage
     *
     * @return array
     */
    protected function getArguments(JobExecutionMessage $jobExecutionMessage): array
    {
        $jobInstanceCode = $this->executionMessageRepository->getJobInstanceCode($jobExecutionMessage);

        $arguments = [
            $jobInstanceCode,
            $jobExecutionMessage->getJobExecutionId(),
        ];

        foreach ($jobExecutionMessage->getOptions() as $optionsName => $optionValue) {
            if (true === $optionValue) {
                $arguments[] = sprintf('--%s', $optionValue);
            }
            if (false !== $optionValue) {
                $arguments[] = sprintf('--%s=%s', $optionsName, $optionValue);
            }
        }

        return $arguments;
    }
}
