<?php

namespace Akeneo\Tool\Component\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command executor
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CommandExecutor
{
    public function __construct(
        protected readonly InputInterface $input,
        protected readonly OutputInterface $output,
        protected readonly Application $application
    ) {
    }

    public function runCommand(string $commandName, array $params = []): static
    {
        $params = array_merge(
            $params,
            $this->getDefaultParams()
        );

        $command = $this->application->find($commandName);

        $commandInput = new ArrayInput($params);
        if ($this->input->hasOption('no-interaction')) {
            $commandInput->setInteractive(false);
        }

        $command->run(
            $commandInput,
            $this->input->hasOption('quiet') ? new NullOutput() : $this->output
        );

        return $this;
    }

    protected function getDefaultParams(): array
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
