<?php

/**
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Component\Console;

use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Command launcher
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class CommandLauncher
{
    /** @var string Application root directory */
    protected $rootDir;

    /** @var string Application execution environment */
    protected $environment;

    /**
     * @param string $rootDir
     * @param string $environment
     */
    public function __construct($rootDir, $environment)
    {
        $this->rootDir     = $rootDir;
        $this->environment = $environment;
    }

    /**
     * @return false|string
     */
    protected function getPhp()
    {
        $pathFinder = new PhpExecutableFinder();

        return $pathFinder->find();
    }

    /**
     * @param string $command
     *
     * @return string
     */
    protected function buildCommandString($command)
    {
        return sprintf(
            '%s %s/console --env=%s %s',
            $this->getPhp(),
            $this->rootDir,
            $this->environment,
            $command
        );
    }

    /**
     * Launch command in background and return
     *
     * @param string $command
     * @param string $logfile
     *
     * @return null
     */
    public function executeBackground($command, $logfile = null)
    {
        $cmd  = $this->buildCommandString($command);
        if (null === $logfile) {
            $logfile = sprintf('%s/logs/command_execute.log', $this->rootDir);
        }
        $cmd .= sprintf(' >> %s 2>&1', $logfile);
        exec($cmd);

        return null;
    }

    /**
     * Launch command and return result
     *
     * @param string $command
     *
     * @return CommandResultInterface
     */
    public function executeForeground($command)
    {
        $cmd    = $this->buildCommandString($command);
        $output = [];
        $status = null;

        exec($cmd, $output, $status);

        $result = new CommandResult($output, $status);

        return $result;
    }
}
