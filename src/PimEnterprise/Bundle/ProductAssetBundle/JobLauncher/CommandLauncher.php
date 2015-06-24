<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\JobLauncher;

use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Job launcher for PAM commands
 *
 * @package PimEnterprise\Bundle\ProductAssetBundle\JobLauncher
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 *
 * TODO: Maybe find another namespace / location.
 */
class CommandLauncher
{
    /** @var string Application root directory */
    protected $rootDir;

    /**
     * @param string $rootDir
     */
    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
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
    public function launchCommand($command, $background = true)
    {
        $cmd = sprintf(
            '%s %s/console %s',
            $this->getPhp(),
            $this->rootDir,
            $command
        );

        if (true === $background) {
            $cmd .= ' &';
        }

        $result = exec($cmd);

        return null;
    }
}
