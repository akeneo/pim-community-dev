<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\FixtureInstaller;

use Akeneo\Platform\Installer\Domain\Service\FixtureInstallerInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class MinimalFixtureInstaller implements FixtureInstallerInterface
{
    public function __construct(private readonly string $projectDir)
    {
    }

    public function installWithoutUsersUserGroupsAndUserRoles(): void
    {
        $pathFinder = new PhpExecutableFinder();
        $process = new Process([
            $pathFinder->find(),
            sprintf('%s/bin/console', $this->projectDir),
            'pim:installer:db',
            '--catalog',
            'src/AkeneoEnterprise/Platform/Installer/back/src/Infrastructure/Symfony/Resources/fixtures/minimal',
            '--fixtures-to-skip',
            'fixtures_user_csv',
            '--fixtures-to-skip',
            'fixtures_user_role_csv',
            '--fixtures-to-skip',
            'fixtures_user_group_csv',
            '--fixtures-to-skip',
            'fixtures_attribute_group_access_csv',
            '--fixtures-to-skip',
            'fixtures_product_category_access_csv',
        ], $this->projectDir);

        $process->setTimeout(null);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception(sprintf('Install failed, "%s".', $process->getOutput().PHP_EOL.$process->getErrorOutput()));
        }
    }
}
