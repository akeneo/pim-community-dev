<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Bundle\InstallerBundle;

use Akeneo\Platform\Bundle\InstallerBundle\ComposerScripts as BaseComposerScripts;
use Composer\Script\Event;
use Symfony\Component\Finder\Finder;

/**
 * Contains all PIM composer scripts we run.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class EnterpriseComposerScripts extends BaseComposerScripts
{
    /**
     * Copy the upgrades/ folder from PIM dependency to the standard edition
     *
     * @param Event $event
     */
    protected static function copyUpgradesFolder(Event $event)
    {
        $eePath = static::getDependencyMigrationPath($event, 'pim-enterprise-dev');
        $migrationFolder = static::getMigrationFolderPath($event);

        static::copyFiles($event, $eePath, $migrationFolder);

        $cePath = static::getDependencyMigrationPath($event, 'pim-community-dev');

        static::copyFiles($event, $cePath, $migrationFolder);
    }

    /**
     * Copy UPGRADE-x.y.md files from PIM dependency to the standard edition
     *
     * @param Event $event
     */
    protected static function copyUpgradeFile(Event $event)
    {
        $finder = new Finder();
        $ds = DIRECTORY_SEPARATOR;

        $eePath = static::getDependencyMigrationPath($event, 'pim-enterprise-dev');
        $eeDepPath = $eePath . '..' . $ds;
        $eeRoot = $event->getComposer()->getConfig()->get('vendor-dir') . $ds . '..' . $ds;

        $finder
            ->files()
            ->name('UPGRADE-*.md')
            ->depth(0);
        foreach ($finder->in($eeDepPath) as $file) {
            static::copyFiles($event, $file->getPathname(), $eeRoot . $file->getFilename());
        }
    }
}
