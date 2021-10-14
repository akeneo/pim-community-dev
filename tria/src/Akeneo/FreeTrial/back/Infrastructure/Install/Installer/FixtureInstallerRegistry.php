<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\Install\Installer;

final class FixtureInstallerRegistry
{
    private array $installers;

    public function __construct()
    {
        $this->installers = [];
    }

    public function addInstaller(FixtureInstaller $installer, string $alias): void
    {
        $this->installers[$alias] = $installer;
    }

    public function getInstaller(string $alias): FixtureInstaller
    {
        if (!array_key_exists($alias, $this->installers)) {
            throw new \Exception(sprintf('Installer %s not found', $alias));
        }

        return $this->installers[$alias];
    }
}
