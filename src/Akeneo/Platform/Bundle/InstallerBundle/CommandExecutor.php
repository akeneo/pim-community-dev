<?php

namespace Akeneo\Platform\Bundle\InstallerBundle;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command executor
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CommandExecutor
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var Application
     */
    protected $application;

    /**
     * Constructor
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param Application     $application
     */
    public function __construct(InputInterface $input, OutputInterface $output, Application $application)
    {
        $this->input = $input;
        $this->output = $output;
        $this->application = $application;
    }

    /**
     * {@inheritdoc}
     */
    public function runCommand($command, $params = [])
    {
        $params = array_merge(
            ['command' => $command],
            $params,
            $this->getDefaultParams()
        );

        $this->application->setAutoExit(false);
        $exitCode = $this->application->run(new ArrayInput($params), $this->output);

        if (0 !== $exitCode) {
            $this->application->setAutoExit(true);
            $errorMessage = sprintf('The command terminated with an error code: %u.', $exitCode);
            $this->output->writeln("<error>$errorMessage</error>");
            $e = new \Exception($errorMessage, $exitCode);
            throw $e;
        }

        return $this;
    }

    /**
     * Get default parameters
     *
     * @return array
     */
    protected function getDefaultParams()
    {
        $defaultParams = ['--no-debug' => true];

        if ($this->input->hasOption('env')) {
            $defaultParams['--env'] = $this->input->getOption('env');
        }

        if ($this->input->hasOption('verbose') && $this->input->getOption('verbose') === true) {
            $defaultParams['--verbose'] = true;
        }

        return $defaultParams;
    }
}
