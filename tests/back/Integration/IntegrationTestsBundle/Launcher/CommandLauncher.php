<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\Launcher;

use Akeneo\Tool\Bundle\BatchBundle\Command\BatchCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Launch commands for the integration tests.
 */
class CommandLauncher
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
     * @param string      $commandName
     * @param string|null $username
     * @param array       $config
     *
     * @throws \Exception
     *
     * @return int
     */
    public function execute(string $commandName, string $username = null, array $config = []) : int
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $arrayInput = [
            'command'  => $commandName,
            '-v'       => true,
        ];

        if (null !== $username) {
            $arrayInput['--username'] = $username;
        }

        $command = $application->find($commandName);
        $commandTester = new CommandTester($command);
        if (isset($config['inputs'])) {
            $commandTester->setInputs($config['inputs']);
        }
        $commandTester->execute($arrayInput);

        $output = $commandTester->getOutput();
        $exitCode = $commandTester->getStatusCode();

        if (BatchCommand::EXIT_SUCCESS_CODE !== $exitCode) {
            throw new \Exception(sprintf('Command "%s" failed, "%s".', $commandName, $output->fetch()));
        }

        return $exitCode;
    }
}
