<?php

namespace Akeneo\Test\Integration;

use Akeneo\Bundle\BatchBundle\Command\BatchCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Launch jobs for the integration tests.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobLauncher
{
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

        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR. 'pim-integration-tests-export' . DIRECTORY_SEPARATOR . 'export.csv';
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

        return file_get_contents($filePath);
    }
}
