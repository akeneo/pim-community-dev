<?php

declare(strict_types=1);

namespace Akeneo\Test\Integration;

use Akeneo\Bundle\BatchBundle\Command\BatchCommand;
use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Model\JobExecution;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Launch jobs for the integration tests.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobLauncher
{
    const EXPORT_DIRECTORY = 'pim-integration-tests-export';

    const IMPORT_DIRECTORY = 'pim-integration-tests-import';

    /** @var KernelInterface */
    protected $kernel;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param string      $command
     * @param string      $jobCode
     * @param string|null $username
     * @param array       $config
     *
     * @throws \Exception
     *
     * @return string
     */
    public function launchExport(string $command, string $jobCode, string $username = null, array $config = []) : string
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR. self::EXPORT_DIRECTORY . DIRECTORY_SEPARATOR . 'export.csv';
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $config['filePath'] = $filePath;

        $arrayInput = [
            'command'  => $command,
            'code'     => $jobCode,
            '--config' => json_encode($config),
            '--no-log' => true,
            '-v'       => true
        ];

        if (null !== $username) {
            $arrayInput['username'] = $username;
        }

        $input = new ArrayInput($arrayInput);

        $output = new BufferedOutput();
        $exitCode = $application->run($input, $output);

        if (BatchCommand::EXIT_SUCCESS_CODE !== $exitCode) {
            throw new \Exception(sprintf('Export failed, "%s".', $output->fetch()));
        }

        if (!is_readable($filePath)) {
            throw new \Exception(sprintf('Exported file "%s" is not readable for the job "%s".', $filePath, $jobCode));
        }

        $content = file_get_contents($filePath);
        unlink($filePath);

        return $content;
    }

    /**
     * Launch an export in a subprocess because it's not possible to launch two exports in the same process.
     * The cause is that some services are stateful, such as the JSONFileBuffer that is not flushed after an export.
     *
     * TODO: fix  stateful services
     *
     * @param string      $command
     * @param string      $jobCode
     * @param string|null $username
     * @param array       $config
     *
     * @throws \Exception
     *
     * @return string
     */
    public function launchSubProcessExport(string $command, string $jobCode, string $username = null, array $config = []) : string
    {
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR. self::EXPORT_DIRECTORY . DIRECTORY_SEPARATOR . 'export_.csv';
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $config['filePath'] = $filePath;

        $pathFinder = new PhpExecutableFinder();
        $command = sprintf(
            '%s %s/console %s --env=%s --config=\'%s\' -v %s',
            $pathFinder->find(),
             sprintf('%s/../bin', $this->kernel->getRootDir()),
            $command,
            $this->kernel->getEnvironment(),
            json_encode($config, JSON_HEX_APOS),
            $jobCode
        );

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
     * @param string      $command
     * @param string      $jobCode
     * @param string      $content
     * @param string|null $username
     * @param array       $fixturePaths
     * @param array       $config
     *
     * @throws \Exception
     */
    public function launchImport(
        string $command,
        string $jobCode,
        string $content,
        string $username = null,
        array $fixturePaths = [],
        array $config = []
    ) : void {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $importDirectoryPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::IMPORT_DIRECTORY;
        $fixturesDirectoryPath = $importDirectoryPath . DIRECTORY_SEPARATOR . 'fixtures';
        $filePath = $importDirectoryPath . DIRECTORY_SEPARATOR . 'import.csv';

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

        $config['filePath'] = $filePath;

        $arrayInput = [
            'command'  => $command,
            'code'     => $jobCode,
            '--config' => json_encode($config),
            '--no-log' => true,
            '-v'       => true
        ];

        if (null !== $username) {
            $arrayInput['username'] = $username;
        }

        $input = new ArrayInput($arrayInput);

        $output = new BufferedOutput();
        $exitCode = $application->run($input, $output);

        if (BatchCommand::EXIT_SUCCESS_CODE !== $exitCode) {
            throw new \Exception(sprintf('Export failed, "%s".', $output->fetch()));
        }
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

        $em = $this->kernel->getContainer()->get('doctrine.orm.default_entity_manager');
        $connection = $em->getConnection();
        $stmt = $connection->prepare('SELECT status from akeneo_batch_job_execution where id = :id');

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
}
