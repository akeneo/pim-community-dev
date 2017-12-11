<?php

namespace Pim\Bundle\InstallerBundle;

use Composer\Script\Event;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Contains all PIM composer scripts we run.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComposerScripts
{
    /**
     * @param Event $event
     */
    public static function copyUpgradesFiles(Event $event)
    {
        $event->getIO()->write('Copying migration folder from Akeneo PIM dependency to standard version.');

        static::copyUpgradesFolder($event);
        static::copyUpgradeFile($event);

        $event->getIO()->write('Done.');
    }

    /**
     * @param Event $event
     */
    public static function warnUserForPendingMigrations(Event $event)
    {
        $event->getIO()->writeError('<warning>Please don\'t forget to execute the pending migrations using the `doctrine:migrations:migrate` console command.</warning>');
    }

    /**
     * Copy the upgrades/ folder from PIM dependency to the standard edition
     *
     * @param Event $event
     */
    protected static function copyUpgradesFolder(Event $event)
    {
        $cePath = static::getDependencyMigrationPath($event, 'pim-community-dev');
        $migrationPath = static::getMigrationFolderPath($event);

        static::copyFiles($event, $cePath, $migrationPath);
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

        $cePath = static::getDependencyMigrationPath($event, 'pim-community-dev');
        $ceDepPath = $cePath . '..' . $ds;
        $ceRoot = $event->getComposer()->getConfig()->get('vendor-dir') . $ds . '..' . $ds;

        $finder
            ->files()
            ->name('UPGRADE-*.md')
            ->depth(0);
        foreach ($finder->in($ceDepPath) as $file) {
            static::copyFiles($event, $file->getPathname(), $ceRoot . $file->getFilename());
        }
    }

    /**
     * @param Event $event
     *
     * @return string
     */
    protected static function getMigrationFolderPath(Event $event)
    {
        $ds = DIRECTORY_SEPARATOR;

        $vendorPath = $event->getComposer()->getConfig()->get('vendor-dir');
        $migrationPath = $vendorPath . $ds . '..' . $ds . 'upgrades' . $ds;

        return $migrationPath;
    }

    /**
     * @param Event  $event
     * @param string $edition
     *
     * @return string
     */
    protected static function getDependencyMigrationPath(Event $event, $edition)
    {
        $ds = DIRECTORY_SEPARATOR;

        $vendorPath = $event->getComposer()->getConfig()->get('vendor-dir');
        $communityPath = $vendorPath . $ds . 'akeneo' . $ds . $edition . $ds . 'upgrades' . $ds;

        return $communityPath;
    }

    /**
     * Copy files from $copyToPath to $destinationPath keeping the $copyToPath architecture
     * This method is able to copy one file and folders
     *
     * @param Event  $event           Composer script event
     * @param string $copyFromPath    Copy files from this folder path
     * @param string $destinationPath Copy files to this folder path
     */
    protected static function copyFiles(Event $event, $copyFromPath, $destinationPath)
    {
        $fs = new Filesystem();
        if (!$fs->exists($copyFromPath)) {
            $event->getIO()->writeError(sprintf(
                'Folder "%s" not found. Update will continue but no Akeneo PIM migration can be done.',
                $copyFromPath
            ));

            return;
        }

        if (is_dir($copyFromPath)) {
            $finder = new Finder();
            static::createMissingFolder($destinationPath);
            foreach ($finder->in($copyFromPath) as $file) {
                if (!is_dir($file->getPathname())) {
                    $filePath = $file->getPathname();

                    $fs->copy($filePath, $destinationPath . $file->getRelativePathname(), true);
                } else {
                    static::createMissingFolder($destinationPath . $file->getRelativePathname());
                }
            }
        } else {
            $fs->copy($copyFromPath, $destinationPath, true);
        }
    }

    /**
     * Sanitize the given path. Create the folder if it doesn't exist.
     *
     * @param string $path
     */
    protected static function createMissingFolder($path)
    {
        $fs = new Filesystem();
        if (!$fs->exists($path)) {
            $fs->mkdir($path, 0777);
        }
    }
}
