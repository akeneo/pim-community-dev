<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\Launcher;

use Akeneo\Tool\Bundle\BatchBundle\Command\BatchCommand;
use AkeneoTest\Integration\IntegrationTestsBundle\Launcher\PubSubQueueStatus;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use League\Flysystem\FilesystemOperator;
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

    /**
     * @param PubSubQueueStatus[] $pubSubQueueStatuses
     */
    public function __construct(
        private KernelInterface $kernel,
        private Connection $connection,
        private iterable $pubSubQueueStatuses,
        private LoggerInterface $logger,
        private FilesystemOperator $jobStorageFilesystem,
        private FilesystemOperator $archivistFilesystem,
    ) {
        Assert::allIsInstanceOf($pubSubQueueStatuses, PubSubQueueStatus::class);
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
        Assert::stringNotEmpty($format);
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR. self::EXPORT_DIRECTORY . DIRECTORY_SEPARATOR . 'export.' . $format;
        $config['storage'] = ['type' => 'none', 'file_path' => $filePath];

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

        $jobExecution = $this->getLastJobExecutionInformation($jobCode);
        $archiveFilename = sprintf('export/%s/%s/output/export.%s', $jobExecution['job_name'], $jobExecution['id'], $format);
        if (!$this->archivistFilesystem->fileExists($archiveFilename)) {
            return '';
        }

        return $this->archivistFilesystem->read($archiveFilename);
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
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR. self::EXPORT_DIRECTORY . DIRECTORY_SEPARATOR . 'export_.csv';
        $config['storage'] = ['type' => 'none', 'file_path' => $filePath];

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

        $jobExecution = $this->getLastJobExecutionInformation($jobCode);
        $archiveFilename = sprintf('export/%s/%s/output/export_.csv', $jobExecution['job_name'], $jobExecution['id']);
        if (!$this->archivistFilesystem->fileExists($archiveFilename)) {
            throw new \Exception(sprintf('Exported file "%s" is not readable for the job "%s".', $filePath, $jobCode));
        }

        return $this->archivistFilesystem->read($archiveFilename);
    }

    /**
     * @param string      $jobCode
     * @param string      $content
     *
     * @throws \Exception
     */
    public function launchFixtureImport(
        string $jobCode,
        string $content,
    ) : void {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $importDirectoryPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR. 'pim-integration-tests-import';
        $fixturesDirectoryPath = $importDirectoryPath . DIRECTORY_SEPARATOR . 'fixtures';
        $filePath = $importDirectoryPath . DIRECTORY_SEPARATOR . 'import.csv';

        $fs = new Filesystem();
        $fs->remove($importDirectoryPath);
        $fs->remove($fixturesDirectoryPath);
        $fs->mkdir($importDirectoryPath);
        $fs->mkdir($fixturesDirectoryPath);

        file_put_contents($filePath, $content);

        $config = ['storage' => ['type' => 'local', 'file_path' => $filePath]];

        $arrayInput = [
            'command'  => 'akeneo:batch:job',
            'code'     => $jobCode,
            '--config' => json_encode($config),
            '--no-log' => true,
            '-v'       => true
        ];

        $input = new ArrayInput($arrayInput);

        $output = new BufferedOutput();
        $exitCode = $application->run($input, $output);

        if (BatchCommand::EXIT_SUCCESS_CODE !== $exitCode) {
            throw new \Exception(sprintf('Export failed, "%s".', $output->fetch()));
        }
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
        Assert::stringNotEmpty($format);
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $importDirectoryPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::IMPORT_DIRECTORY;
        $fixturesDirectoryPath = $importDirectoryPath . DIRECTORY_SEPARATOR . 'fixtures';
        $filePath = $importDirectoryPath . DIRECTORY_SEPARATOR . 'import.' . $format;

        $this->jobStorageFilesystem->deleteDirectory($importDirectoryPath);
        $this->jobStorageFilesystem->deleteDirectory($fixturesDirectoryPath);
        $this->jobStorageFilesystem->write($filePath, $content);

        foreach ($fixturePaths as $fixturePath) {
            $fixturesPath = $fixturesDirectoryPath . DIRECTORY_SEPARATOR . basename($fixturePath);
            $content = file_get_contents($fixturesPath);
            $this->jobStorageFilesystem->write($filePath, $content);
        }

        $config['storage'] = ['type' => 'manual_upload', 'file_path' => $filePath];

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
        $importDirectoryPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::IMPORT_DIRECTORY;
        $fixturesDirectoryPath = $importDirectoryPath . DIRECTORY_SEPARATOR . 'fixtures';
        $filePath = $importDirectoryPath . DIRECTORY_SEPARATOR . 'import.csv';

        $this->jobStorageFilesystem->deleteDirectory($importDirectoryPath);
        $this->jobStorageFilesystem->deleteDirectory($fixturesDirectoryPath);
        $this->jobStorageFilesystem->write($filePath, $content);

        foreach ($fixturePaths as $fixturePath) {
            $fixturesPath = $fixturesDirectoryPath . DIRECTORY_SEPARATOR . basename($fixturePath);
            $content = file_get_contents($fixturesPath);
            $this->jobStorageFilesystem->write($filePath, $content);
        }

        $config['storage'] = ['type' => 'manual_upload', 'file_path' => $filePath];

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

    private function getLastJobExecutionInformation(string $jobCode): array
    {
        $query = <<<SQL
            SELECT je.id, ji.job_name
            FROM akeneo_batch_job_instance ji
            JOIN akeneo_batch_job_execution je ON je.job_instance_id = ji.id
            WHERE ji.code = :code
            ORDER BY je.id DESC
            LIMIT 1
        SQL;

        return $this->connection->executeQuery($query, ['code' => $jobCode])->fetchAssociative();
    }
}
