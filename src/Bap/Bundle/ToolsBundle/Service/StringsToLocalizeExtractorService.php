<?php
namespace Bap\Bundle\ToolsBundle\Service;

use Bap\Bundle\ToolsBundle\Factory\TranslatorFactory;

use Symfony\Component\Translation\Dumper\YamlFileDumper;

use Symfony\Component\Translation\Loader\YamlFileLoader;

use Symfony\Component\Translation\MessageCatalogue;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Create i18n missing files and fill up with existing keys
 * Return a list of unused keys in the source code
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class StringsToLocalizeExtractorService
{
    /**
     * I18n directory path from bundle path
     * @staticvar string
     */
    protected static $i18nPath = '/Resources/translations/';

    protected static $untranslatedChar = '##';

    /**
     * Source directory
     * @var string
     */
    protected $sourcePath;

    /**
     * List of bundles
     * @var multitype:string
     */
    protected $bundles;

    /**
     * List of used locales
     * @var multitype:string
     */
    protected $locales;

    /**
     * Create all missing i18n files and append keys used
     * @param string  $sourcePath Source path to extract strings
     * @param string  $format     files format for i18n (yml, xliff, php, ...)
     * @param boolean $forcedCopy forced copy for unexisting translation directories
     *
     * @return multitype:string
     */
    public function extractStringsToLocalize($sourcePath, $format = 'yml', $forcedCopy = false)
    {
        // initialize instance variables
        $this->sourcePath = $sourcePath;
        $this->format     = $format;
        $this->forcedCopy = $forcedCopy;

        $uselessKeys = array();

        foreach ($this->getBundles() as $bundle) {
            $i18nPath = self::getI18nPath($bundle);

            // if directory not exists, no translation files to copy
            if (!is_dir($i18nPath)) {
                if ($this->forcedCopy) {
                    $filesystem = new Filesystem();
                    $filesystem->mkdir($i18nPath);
                } else {
                    continue;
                }
            }

            // 3. Add missing files
            $this->createMissingFiles($i18nPath);

            // 4. Fill up i18n key for each files
            $this->fillUpKeys($i18nPath);

            // 6. Add undefined i18n keys in message file
            $this->fillUpUndefinedKeys($bundle);

            // 5. Detect useless keys
            $bundleUselessKeys = $this->detectUselessKeys($bundle);
            $uselessKeys = array_merge($uselessKeys, $bundleUselessKeys);

            // 7. remove ~ files is asked
            $this->removeBackupFiles($i18nPath);
        }

        return $uselessKeys;
    }

    /**
     * Return i18n directory from bundle path
     * @param string $bundlePath
     *
     * @return string
     * @static
     */
    protected static function getI18nPath($bundlePath)
    {
        return $bundlePath . self::$i18nPath;
    }

    /**
     * Create i18n missing files
     * @param string $i18nPath
     */
    public function createMissingFiles($i18nPath)
    {
        // get i18n filenames
        $i18nFilenames = $this->extractI18nFilenames($i18nPath);

        // create messages file if not exists (default file)
        if (!in_array('messages', $i18nFilenames)) {
            $i18nFilenames[] = 'messages';
        }

        // create filesystem object
        $filesystem = new Filesystem();

        foreach ($i18nFilenames as $filename) {
            foreach ($this->getLocales() as $locale) {
                $i18nFile = $i18nPath . $filename .'.'. $locale .'.yml'; // TODO : extension must be a parameter ?

                // if file not exists, create it
                if (!file_exists($i18nFile)) {
                    $filesystem->touch($i18nFile);
                    echo "\t\t--> create file ". $i18nFile ."\n";
                }
            }
        }
    }

    /**
     * Fill up i18n keys for each locale/file
     * @param string $i18nPath
     */
    public function fillUpKeys($i18nPath)
    {
        // get i18n filenames
        $filenames = $this->extractI18nFilenames($i18nPath);
        $loader = $this->loaderFactory('yml');
        $dumper = $this->dumperFactory('yml');

        foreach ($filenames as $filename) {
            // all keys for a domain (filename)
            $i18nMasterKeys = $this->getMasterKeys($i18nPath, $filename);

            // for each file, set i18n domain strings to translate with or not translated values
            foreach ($this->getLocales() as $locale) {
                $i18nFile = $i18nPath . $filename .'.'. $locale .'.yml'; // TODO : extension must be a parameter

                $messageCatalogue = $loader->load($i18nFile, $locale, $filename);
                foreach ($i18nMasterKeys as $i18nKey) {
                    // create unexistent values
                    if (!$messageCatalogue->has($i18nKey, $filename)) {
                        $defaultValue = self::formatI18nDefaultValue($i18nKey); // TODO : move in helper or formatter
                        $messageCatalogue->set($i18nKey, $defaultValue, $filename);
                    }
                }

                // call dumper to dump content for each domain
                $dumper->dump($messageCatalogue, array('path' => $i18nPath));
            }
        }
    }

    /**
     * format an untranslated key
     * @param string $i18nKey
     *
     * @return string
     */
    protected static function formatI18nDefaultValue($i18nKey)
    {
        return self::$untranslatedChar . $i18nKey . self::$untranslatedChar;
    }

    /**
     * Get all keys translated for a domain
     * @param string $i18nPath the path for translation files
     * @param string $domain   the translation content domain
     *
     * @return multitype:string
     */
    protected function getMasterKeys($i18nPath, $domain)
    {
        $masterCatalogue = $this->getMasterCatalogue($i18nPath, $domain);
        $i18nMaster = $masterCatalogue->all($domain);

        return array_keys($i18nMaster);
    }

    /**
     * Create a master catalogue with all i18n keys/values for a domain
     * @param string $i18nPath the path for translation files
     * @param string $domain   the translation content domain
     *
     * @return \Symfony\Component\Translation\MessageCatalogue
     */
    protected function getMasterCatalogue($i18nPath, $domain)
    {
        $locales = $this->locales;
        $masterCatalogue = null;
        $loader = $this->loaderFactory('yml');

        foreach ($locales as $locale) {
            $i18nFile = $i18nPath . $domain .'.'. $locale .'.yml'; // TODO : extension must be a parameter

            if ($masterCatalogue === null) {
                $masterCatalogue = $loader->load($i18nFile, $locales, $domain);
            } else {
                $messageCatalogue = $loader->load($i18nFile, $locales, $domain);
                $masterCatalogue->addCatalogue($messageCatalogue);
            }
        }

        return $masterCatalogue;
    }

    /**
     * Add keys used in a bundle (but not defined in translation files) to i18n message file
     * @param string $bundlePath
     */
    public function fillUpUndefinedKeys($bundlePath)
    {
        // get all keys used in a bundle
        $i18nKeys = $this->extractI18nKeys($bundlePath);

        // get all translated keys in a bundle
        $i18nPath = self::getI18nPath($bundlePath);
        $i18nDefinedKeys = array();
        foreach ($this->extractI18nFilenames($i18nPath) as $domain) {
            $domainKeys = $this->getMasterKeys($i18nPath, $domain);
            $i18nDefinedKeys = array_merge($i18nDefinedKeys, $domainKeys);
        }

        // get all undefined keys
        $undefinedKeys = array_diff($i18nKeys, $i18nDefinedKeys);

        // add keys in i18n message file
        $loader = $this->loaderFactory('yml');    // TODO : extension must be a parameter
        $dumper = $this->dumperFactory('yml');    // TODO : extension must be a parameter

        foreach ($this->getLocales() as $locale) {
            $i18nFile = $i18nPath .'messages.'. $locale .'.yml';

            // complete catalogue with undefined keys
            $messageCatalogue = $loader->load($i18nFile, $locale, $domain);
            foreach ($undefinedKeys as $undefinedKey) {
                $defaultValue = self::formatI18nDefaultValue($undefinedKey);
                $messageCatalogue->set($undefinedKey, $defaultValue, 'messages');
            }

            // dump messages file
            $dumper->dump($messageCatalogue, array('path' => $i18nPath));
        }
    }

    /**
     * Extract all i18n keys used in a bundle
     * @param string $bundlePath
     *
     * @return Ambigous <\Bap\Bundle\ToolsBundle\Service\multiple:string, multitype:>
     */
    protected function extractI18nKeys($bundlePath)
    {
        $extractor = new FinderExtractorService();
        $i18nKeys = $extractor->extractI18nKeys($bundlePath);

        return $i18nKeys;
    }

    /**
     * Detect useless keys in bundle code
     * @param string $bundlePath
     *
     * @return multitype:string
     */
    public function detectUselessKeys($bundlePath)
    {
        // get all keys used in a bundle
        $i18nKeys = $this->extractI18nKeys($bundlePath);

        // get all translated keys in a bundle
        $i18nPath = self::getI18nPath($bundlePath);
        $i18nDefinedKeys = array();
        foreach ($this->extractI18nFilenames($i18nPath) as $domain) {
            $domainKeys = $this->getMasterKeys($i18nPath, $domain);
            $i18nDefinedKeys = array_merge($i18nDefinedKeys, $domainKeys);
        }

        // get all useless keys
        $uselessKeys = array_diff($i18nDefinedKeys, $i18nKeys);

        return $uselessKeys;
    }

    /**
     * Remove backup files
     * @param string $path
     */
    public function removeBackupFiles($path)
    {
        $files = $this->extractI18nBackupFiles($path);

        $filesystem = new Filesystem();
        $filesystem->remove($files);
    }

    /**
     * Call factory method to create loader in corresponding format
     * @param string $format
     *
     * @return \Symfony\Component\Translation\Loader\LoaderInterface
     * @throws \Exception
     */
    protected function loaderFactory($format)
    {
        return TranslatorFactory::createLoader('yml');
    }

    /**
     * Call factory method to create dumper in corresponding format
     * @param string $format
     *
     * @return \Symfony\Component\Translation\Dumper\DumperInterface
     * @throws \Exception
     */
    protected function dumperFactory($format)
    {
        return TranslatorFactory::createDumper($format);
    }

    /**
     * Extract bundles path from source path
     *
     * @return multitype:string
     */
    protected function extractBundles()
    {
        $extractor = new FinderExtractorService();
        $bundles = $extractor->extractBundles($this->sourcePath);

        return $bundles;
    }

    /**
     * Extract locales from source path
     *
     * @return multitype:string
     */
    protected function extractLocales()
    {
        $extractor = new FinderExtractorService();
        $locales = $extractor->extractLocales($this->sourcePath);

        return $locales;
    }

    /**
     * Extract i18n filenames
     * @param string $path
     *
     * @return multitype:string
     *
     * TODO : extract domain AND extensions ?
     * TODO : must be rename extractDomains
     */
    protected function extractI18nFilenames($path)
    {
        $extractor = new FinderExtractorService();
        $filenames = $extractor->extractI18nFilenames($path);

        return $filenames;
    }

    /**
     * Extract i18n backup files (~)
     * @param string $path
     *
     * @return multitype:string
     */
    protected function extractI18nBackupFiles($path)
    {
        $extractor = new FinderExtractorService();
        $files = $extractor->extractBackupFiles($path);

        return $files;
    }

    /**
     * Get source directory
     * @return string
     */
    protected function getSourceDirectory()
    {
        return $this->sourcePath;
    }

    /**
     * Get extracted locales
     *
     * @return multitype:string
     */
    protected function getLocales()
    {
        if (!$this->locales) {
            $this->locales = $this->extractLocales();
        }

        return $this->locales;
    }

    /**
     * Get extracted bundles
     *
     * @return multitype:string
     */
    protected function getBundles()
    {
        if (!$this->bundles) {
            $this->bundles = $this->extractBundles();
        }

        return $this->bundles;
    }
}