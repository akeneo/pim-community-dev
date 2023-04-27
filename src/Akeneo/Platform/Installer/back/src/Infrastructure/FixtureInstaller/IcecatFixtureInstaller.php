<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Installer\Infrastructure\FixtureInstaller;

use Akeneo\Platform\Installer\Domain\Service\FixtureInstallerInterface;
use Akeneo\Tool\Bundle\BatchBundle\Command\BatchCommand;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class IcecatFixtureInstaller implements FixtureInstallerInterface
{
    public function __construct(private string $projectDir)
    {
    }

    public function install(): void
    {
        $pathFinder = new PhpExecutableFinder();
        $process = new Process([
            $pathFinder->find(),
            sprintf('%s/bin/console', $this->projectDir),
            'pim:installer:db',
            '--catalog',
            'src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/minimal',
        ], $this->projectDir);

        $process->setTimeout(null);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception(sprintf('Install failed, "%s".', $process->getOutput() . PHP_EOL . $process->getErrorOutput()));
        }

        $process = new Process([
            $pathFinder->find(),
            sprintf('%s/bin/console', $this->projectDir),
            'pim:user:create',
            '--admin',
            '-n',
            '--',
            'admin',
            'admin',
            'test@example.com',
            'John',
            'Doe',
            'en_US'
        ], $this->projectDir);

        $process->setTimeout(null);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception(sprintf('Install failed, "%s".', $process->getOutput() . PHP_EOL . $process->getErrorOutput()));
        }
    }
}
