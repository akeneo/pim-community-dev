<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Component\Console;

use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Job launcher for commands
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
     * @param        $environment
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
     * Launch command
     *
     * @param string $command
     * @param bool   $background
     *
     * @return null
     */
    public function execute($command, $background = true)
    {
        $cmd = sprintf(
            '%s %s/console --env=%s %s',
            $this->getPhp(),
            $this->rootDir,
            $this->environment,
            $command
        );

        if (true === $background) {
            $cmd .= ' &';
        }

        exec($cmd);

        return null;
    }
}
