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

namespace Akeneo\FreeTrial\Infrastructure\Install;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;

trait InstallCatalogTrait
{
    private function isFreeTrialCatalogInstallation(InstallerEvent $installerEvent): bool
    {
        $installedCatalog = $installerEvent->getArgument('catalog');

        return is_string($installedCatalog) && strpos($installedCatalog, 'free_trial_catalog') !== false;
    }

    private function getFixturesPath(): string
    {
        return __DIR__ . '/fixtures/free_trial_catalog';
    }

    private function getJobsFixturesPath(): string
    {
        return $this->getFixturesPath() . '/jobs.yml';
    }
}
