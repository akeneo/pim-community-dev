<?php
namespace Bap\Bundle\ToolsBundle\Service;

use Symfony\Component\Finder\Finder;

/**
 * Service extractor working with Finder
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ExtractorService
{

    /**
     * i18n files pattern
     * @staticvar string
     */
    protected static $i18nFilesPattern = '/(:?.+)\.(\w{2}|\w{2}_\w{2})\.(:?xliff|yml|php)$/';

    /**
     * bundle directories pattern
     * @staticvar string
     */
    protected static $bundleNamePattern = '/^(Catalog)Bundle$/';

    /**
     * backup files pattern
     * @staticvar string
     */
    protected static $backupFilesPattern = '/~$/';

    /**
     * Return new Finder instance
     * @return Finder
     */
    protected function getFinder()
    {
        return Finder::create();
    }

    /**
     * Get all locales used in $path
     * @param string $path
     *
     * @return multitype:string
     */
    public function extractLocales($path)
    {
        $locales = array();

        $finder = $this->getFinder()->files()->name(self::$i18nFilesPattern)->in($path);
        foreach ($finder as $file) {
            if (preg_match(self::$i18nFilesPattern, $file->getFileName(), $matches)) {
                if (!empty($matches[2]) && !in_array($matches[2], $locales)) {
                    $locales[] = $matches[2];
                }
            }
        }

        return $locales;
    }

    /**
     * Get all bundles directories
     * @param string $path
     *
     * @return multitype:string
     */
    public function extractBundles($path)
    {
        $bundlesDirectories = array();

        $finder = $this->getFinder()->directories()->name(self::$bundleNamePattern)->in($path);
        foreach ($finder as $directory) {
            $bundlesDirectories[] = $directory->getRealPath();
        }

        return $bundlesDirectories;
    }

    /**
     * Extract i18n filenames
     * @param string $path
     *
     * @return multitype:string
     */
    public function extractI18nFilenames($path)
    {
        $i18nFilenames = array();

        $finder = $this->getFinder()->files()->name(self::$i18nFilesPattern)->in($path);
        foreach ($finder as $file) {
            if (preg_match(self::$i18nFilesPattern, $file->getFileName(), $matches)) {
                if (!empty($matches[1])) {
                    $i18nFilenames[] = $matches[1];
                }
            }
        }

        return array_unique($i18nFilenames);
    }

    /**
     * Extract backup files
     * @param string $path
     *
     * @return multitype:string
     */
    public function extractBackupFiles($path)
    {
        $files = array();

        $finder = $this->getFinder()->files()->name(self::$backupFilesPattern)->in($path);
        foreach ($finder as $file) {
            $files[] = $file->getRealpath();
        }

        return array_unique($files);
    }
}
