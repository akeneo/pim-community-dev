<?php

namespace Pim\Bundle\InstallerBundle;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Application;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ProcessBuilder;

use Oro\Bundle\InstallerBundle\CommandExecutor as OroCommandExecutor;

/**
 * Command executor
 * Just override Oro Platform CommandExecutor to exit with code 1 if error
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CommandExecutor extends OroCommandExecutor
{
    /**
     * {@inheritdoc}
     */
    public function runCommand($command, $params = array())
    {
        $params = array_merge(
            array(
                'command'    => $command,
                '--no-debug' => true,
            ),
            $params
        );

        $this->application->setAutoExit(false);
        $exitCode = $this->application->run(new ArrayInput($params), $this->output);

        if (0 !== $exitCode) {
            $this->output->writeln(
                sprintf('<error>The command terminated with an error code: %u.</error>', $exitCode)
            );
            exit($exitCode);
        }

        return $this;
    }
}
