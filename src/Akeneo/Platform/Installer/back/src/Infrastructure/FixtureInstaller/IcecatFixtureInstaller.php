<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
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
