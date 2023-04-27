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
        $console = sprintf('%s%sbin%sconsole', $this->projectDir, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);

        $process = new Process([
            $pathFinder->find(),
            $console,
            'pim:installer:db',
            '--catalog',
            'src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/icecat_demo_dev',
        ]);

        $process->setTimeout(null);
        $process->run();
    }
}
