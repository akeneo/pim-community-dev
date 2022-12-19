<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\Launcher;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Tool\Bundle\BatchBundle\Command\BatchCommand;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use AkeneoTest\Integration\IntegrationTestsBundle\Launcher\PubSubQueueStatus;
use Doctrine\DBAL\Driver\Connection;
use Google\Cloud\PubSub\Message;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;

/**
 * Launch jobs for the integration tests.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobLauncher
{
    private const MESSENGER_COMMAND_NAME = 'messenger:consume';
    private const MESSENGER_RECEIVERS = ['data_maintenance_job', 'import_export_job', 'ui_job'];

    const EXPORT_DIRECTORY = 'pim-integration-tests-export';

    const IMPORT_DIRECTORY = 'pim-integration-tests-import';

    private KernelInterface $kernel;
    private Connection $dbConnection;
    /** @var PubSubQueueStatus[] */
    private iterable $pubSubQueueStatuses;
    private LoggerInterface $logger;
    private FeatureFlags $featureFlags;

    public function __construct(
        KernelInterface $kernel,
        Connection $dbConnection,
        iterable $pubSubQueueStatuses,
        LoggerInterface $logger,
        FeatureFlags $featureFlags
    ) {
        Assert::allIsInstanceOf($pubSubQueueStatuses, PubSubQueueStatus::class);
        $this->kernel = $kernel;
        $this->dbConnection = $dbConnection;
        $this->pubSubQueueStatuses = $pubSubQueueStatuses;
        $this->logger = $logger;
        $this->featureFlags = $featureFlags;
    }

    /**
     * @param string      $jobCode
     * @param string|null $username
     * @param array       $config
     *
     * @throws \Exception
     *
     * @return string
     */
    public function launchExport(string $jobCode, string $username = null, array $config = [], string $format = 'csv') : string
    {
        $this->featureFlags->enable('import_export_local_storage');
        Assert::stringNotEmpty($format);
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR. self::EXPORT_DIRECTORY . DIRECTORY_SEPARATOR . 'export.' . $format;
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $config['storage'] = ['type' => 'local', 'file_path' => $filePath];

        $arrayInput = [
            'command'  => 'akeneo:batch:job',
            'code'     => $jobCode,
            '--config' => json_encode($config),
            '--no-log' => true,
            '-v'       => true
        ];

        if (null !== $username) {
            $arrayInput['--username'] = $username;
        }

        $input = new ArrayInput($arrayInput);

        $output = new BufferedOutput();
        $exitCode = $application->run($input, $output);

        if (BatchCommand::EXIT_SUCCESS_CODE !== $exitCode) {
            throw new \Exception(sprintf('Export failed, "%s".', $output->fetch()));
        }

        if (!is_readable($filePath)) {
            return '';
        }

        $content = file_get_contents($filePath);
        unlink($filePath);

        return $content;
    }

    /**
     * @param string      $jobCode
     * @param string|null $username
     * @param array       $config
     *
     * @throws \Exception
     *
     * @return string
     */
    public function launchAuthenticatedExport(string $jobCode, string $username = null, array $config = []) : string
    {
        $config['is_user_authenticated'] = true;

        return self::launchExport($jobCode, $username, $config);
    }

    /**
     * Launch an export in a subprocess because it's not possible to launch two exports in the same process.
     * The cause is that some services are stateful, such as the JSONFileBuffer that is not flushed after an export.
     *
     * TODO: fix  stateful services
     *
     * @param string      $jobCode
     * @param string|null $username
     * @param array       $config
     *
     * @throws \Exception
     *
     * @return string
     */
    public function launchSubProcessExport(string $jobCode, string $username = null, array $config = []) : string
    {
        $this->featureFlags->enable('import_export_local_storage');
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR. self::EXPORT_DIRECTORY . DIRECTORY_SEPARATOR . 'export_.csv';
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $config['storage'] = ['type' => 'local', 'file_path' => $filePath];

        $pathFinder = new PhpExecutableFinder();
        $command = [
            $pathFinder->find(),
            sprintf('%s/bin/console', $this->kernel->getProjectDir()),
            'akeneo:batch:job',
            sprintf('--env=%s',$this->kernel->getEnvironment()),
            sprintf("--config=%s", json_encode($config, JSON_HEX_APOS)),
            '-v',
            $jobCode
        ];

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception(sprintf('Export failed, "%s".', $process->getOutput() . PHP_EOL . $process->getErrorOutput()));
        }

        if (!is_readable($filePath)) {
            throw new \Exception(sprintf('Exported file "%s" is not readable for the job "%s".', $filePath, $jobCode));
        }

        $content = file_get_contents($filePath);
        unlink($filePath);

        return $content;
    }

    /**
     * @param string      $jobCode
     * @param string      $content
     * @param string|null $username
     * @param array       $fixturePaths
     * @param array       $config
     *
     * @throws \Exception
     */
    public function launchImport(
        string $jobCode,
        string $content,
        string $username = null,
        array $fixturePaths = [],
        array $config = [],
        string $format = 'csv'
    ) : void {
        $this->featureFlags->enable('import_export_local_storage');
        Assert::stringNotEmpty($format);
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $importDirectoryPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::IMPORT_DIRECTORY;
        $fixturesDirectoryPath = $importDirectoryPath . DIRECTORY_SEPARATOR . 'fixtures';
        $filePath = $importDirectoryPath . DIRECTORY_SEPARATOR . 'import.' . $format;

        $fs = new Filesystem();
        $fs->remove($importDirectoryPath);
        $fs->remove($fixturesDirectoryPath);
        $fs->mkdir($importDirectoryPath);
        $fs->mkdir($fixturesDirectoryPath);

        file_put_contents($filePath, $content);

        foreach ($fixturePaths as $fixturePath) {
            $fixturesPath = $fixturesDirectoryPath . DIRECTORY_SEPARATOR . basename($fixturePath);
            $fs->copy($fixturePath, $fixturesPath, true);
        }

        $config['storage'] = ['type' => 'local', 'file_path' => $filePath];

        $arrayInput = [
            'command'  => 'akeneo:batch:job',
            'code'     => $jobCode,
            '--config' => json_encode($config),
            '--no-log' => true,
            '-v'       => true
        ];

        if (null !== $username) {
            $arrayInput['--username'] = $username;
        }

        $input = new ArrayInput($arrayInput);

        $output = new BufferedOutput();
        $exitCode = $application->run($input, $output);

        if (!\in_array($exitCode, [BatchCommand::EXIT_SUCCESS_CODE, BatchCommand::EXIT_WARNING_CODE])) {
            throw new \Exception(sprintf('Import failed, "%s".', $output->fetch()));
        }
    }

    /**
     * @param string      $jobCode
     * @param string      $content
     * @param string|null $username
     * @param array       $fixturePaths
     * @param array       $config
     */
    public function launchAuthenticatedImport(
        string $jobCode,
        string $content,
        string $username = null,
        array $fixturePaths = [],
        array $config = []
    ) :void {
        $config['is_user_authenticated'] = true;

        self::launchImport($jobCode, $content, $username, $fixturePaths, $config);
    }

    /**
     * Wait until a job has been finished.
     *
     * @param JobExecution $jobExecution
     *
     * @throws \RuntimeException
     */
    public function waitCompleteJobExecution(JobExecution $jobExecution): void
    {
        $timeout = 0;
        $isCompleted = false;

        $stmt = $this->dbConnection->prepare('SELECT status from akeneo_batch_job_execution where id = :id');

        while (!$isCompleted) {
            if ($timeout > 30) {
                throw new \RuntimeException(sprintf('Timeout: job execution "%s" is not complete.', $jobExecution->getId()));
            }
            $stmt->bindValue('id', $jobExecution->getId());
            $stmt->execute();
            $result = $stmt->fetch();

            $isCompleted = isset($result['status']) && BatchStatus::COMPLETED === (int) $result['status'];

            $timeout++;

            sleep(1);
        }
    }

    /**
     * Indicates whether the queue has a job still not consumed in the queue.
     */
    public function hasJobInQueue(): bool
    {
        /** @var PubSubQueueStatus $pubSubStatus */
        foreach ($this->pubSubQueueStatuses as $pubSubStatus) {
            if ($pubSubStatus->hasMessageInQueue()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns all the messages in the queues
     *
     * @return Message[]
     */
    public function getMessagesInQueues(): array
    {
        return array_merge(...array_map(
            fn (PubSubQueueStatus $pubSubQueueStatus): array => $pubSubQueueStatus->getMessagesInQueue(),
            is_array($this->pubSubQueueStatuses)
                ? $this->pubSubQueueStatuses
                : iterator_to_array($this->pubSubQueueStatuses)
        ));
    }

    /**
     * Launch the daemon command to consume and launch one job execution.
     */
    public function launchConsumerOnce(array $options = []): OutputInterface
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $arrayInput = array_merge(
            $options,
            [
                'command'  => static::MESSENGER_COMMAND_NAME,
                'receivers' => static::MESSENGER_RECEIVERS,
                '--limit' => 1,
            ]
        );

        $input = new ArrayInput($arrayInput);
        $output = new BufferedOutput();
        $application->run($input, $output);

        return $output;
    }

    public function launchConsumerUntilQueueIsEmpty(array $options = [], int $limit = 50): int
    {
        $numberOfJobs = 0;
        while ($this->hasJobInQueue()) {
            $this->launchConsumerOnce($options);
            $numberOfJobs++;
            Assert::notSame($numberOfJobs, $limit, sprintf('Error: the test reaches the limit number of jobs (%d).', $limit));
        }

        return $numberOfJobs;
    }

    /**
     * Launch the daemon command to consume and launch one job execution, in a detached process in background.
     * It uses exec to not wrap the process in a subshell, in order to get the correct pid.
     *
     * @see https://github.com/symfony/symfony/issues/5759
     */
    public function launchConsumerOnceInBackground(int $timeLimitInSeconds = null): Process
    {
        $command = array_merge(
            [
                sprintf('%s/bin/console', $this->kernel->getContainer()->getParameter('kernel.project_dir')),
                static::MESSENGER_COMMAND_NAME,
                sprintf('--env=%s', $this->kernel->getEnvironment()),
                '--limit=1',
                '--verbose',
            ],
            static::MESSENGER_RECEIVERS,
        );

        if (null !== $timeLimitInSeconds) {
            $command[] = sprintf('--time-limit=%d', $timeLimitInSeconds);
        }

        $process = new Process($command);
        $process->start(function (string $type, string $data) {
            if ($type === Process::ERR) {
                $this->logger->error($data);
            } else {
                $this->logger->info($data);
            }
        });

        return $process;
    }

    /**
     * Launch an import in a subprocess.
     *
     * @throws \Exception
     */
    public function launchSubProcessImport(
        string $jobCode,
        string $content,
        ?string $username = null,
        array $fixturePaths = [],
        array $config = []
    ): void {
        $this->featureFlags->enable('import_export_local_storage');

        $importDirectoryPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::IMPORT_DIRECTORY;
        $fixturesDirectoryPath = $importDirectoryPath . DIRECTORY_SEPARATOR . 'fixtures';
        $filePath = $importDirectoryPath . DIRECTORY_SEPARATOR . 'import.csv';

        $fs = new Filesystem();
        $fs->remove($importDirectoryPath);
        $fs->remove($fixturesDirectoryPath);
        $fs->mkdir($importDirectoryPath);
        $fs->mkdir($fixturesDirectoryPath);

        foreach ($fixturePaths as $fixturePath) {
            $fixturesPath = $fixturesDirectoryPath . DIRECTORY_SEPARATOR . basename($fixturePath);
            $fs->copy($fixturePath, $fixturesPath, true);
        }

        file_put_contents($filePath, $content);

        $config['storage'] = ['type' => 'local', 'file_path' => $filePath];

        $pathFinder = new PhpExecutableFinder();
        $command = [
            $pathFinder->find(),
            sprintf('%s/bin/console', $this->kernel->getProjectDir()),
            'akeneo:batch:job',
            sprintf('--env=%s',$this->kernel->getEnvironment()),
            sprintf("--config=%s", json_encode($config, JSON_HEX_APOS)),
            '-v',
            $jobCode
        ];
        if (null !== $username) {
            $command[] = sprintf('--username=%s', $username);
        }

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful() && !BatchCommand::EXIT_WARNING_CODE === $process->getExitCode()) {
            throw new \Exception(sprintf('Import failed, "%s".', $process->getOutput() . PHP_EOL . $process->getErrorOutput()));
        }
    }
    /**
     * Launch an import in a subprocess.
     *
     * @param string $jobCode
     * @param string $content
     * @param string $username
     * @param array  $fixturePaths
     * @param array  $config
     *
     * @throws \Exception
     */
    public function launchAuthenticatedSubProcessImport(
        string $jobCode,
        string $content,
        string $username,
        array $fixturePaths = [],
        array $config = []
    ): void {
        $config['is_user_authenticated'] = true;

        self::launchSubProcessImport($jobCode, $content, $username, $fixturePaths, $config);
    }

    public function flushJobQueue(): void
    {
        foreach ($this->pubSubQueueStatuses as $pubSubStatus) {
            $subscription = $pubSubStatus->getSubscription();
            try {
                $subscription->reload();
            } catch (\Exception $e) {
            }
            if (!$subscription->exists()) {
                continue;
            }

            do {
                $messages = $subscription->pull(['maxMessages' => 10, 'returnImmediately' => true]);
                $count = count($messages);
                if ($count > 0) {
                    $subscription->acknowledgeBatch($messages);
                }
            } while (0 < $count);
        }
    }
}
