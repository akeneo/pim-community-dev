<?php
namespace Bap\Bundle\ToolsBundle\Service;

use Symfony\Component\Translation\Dumper\YamlFileDumper;

use Symfony\Component\Translation\Loader\YamlFileLoader;

use Symfony\Component\Translation\MessageCatalogue;

use Symfony\Component\Filesystem\Filesystem;

/**
 *
 * Enter description here ...
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

    protected $bundles;

    protected $locales;

    protected $i18nKeys;

    public function extractStringsToLocalize($sourcePath)
    {
        $this->sourcePath = $sourcePath;

        foreach ($this->getBundles() as $bundle) {
            // 3. Add missing files
            $i18nPath = $bundle . self::$i18nPath;
            // if directory not exists, no translation files to copy //TODO : Make a forced copy
            if (!is_dir($i18nPath)) {
                continue;
            }
            $this->createMissingFiles($i18nPath);

            // 4. Fill up i18n key for each files
            $this->fillUp($i18nPath);


            // 5. Add undefined i18n keys in message file
            $this->fillUpUndefinedKeys($bundle);


            // 6. Detect useless keys


            // 7. remove ~ files is asked
            $this->removeBackupFiles($i18nPath);

        }
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
                $i18nFile = $i18nPath . $filename .'.'. $locale .'.yml'; // TODO : extension must be a parameter ? get default locale ?

                // if file not exists, create it
                if (!file_exists($i18nFile)) {
                    $filesystem->touch($i18nFile);
                    echo "\t\t--> create file ". $i18nFile ."\n";
                }
            }
        }

        // create messages files if not exists
//         if (!in_array('message', $filenames)) {
//         }
    }

    /**
     * Fill up i18n keys for each locale/file
     * @param string $i18nPath
     */
    public function fillUp($i18nPath)
    {
        // get i18n filenames
        $filenames = $this->extractI18nFilenames($i18nPath);

        foreach ($filenames as $filename) {
            // all keys for a domain (filename)
            $masterCatalogue = $this->getMasterCatalogue($i18nPath, $filename);
            $i18nMaster = $masterCatalogue->all($filename);
            $i18nKeys = array_keys($i18nMaster);

            // for each file, set i18n domain strings to translate with or not translated values
            $loader = $this->loaderFactory('yml');
            $dumper = $this->dumperFactory('yml');

            foreach ($this->getLocales() as $locale) {
                $i18nFile = $i18nPath . $filename .'.'. $locale .'.yml'; // TODO : extension must be a parameter

                $messageCatalogue = $loader->load($i18nFile, $locale, $filename);
                foreach ($i18nKeys as $i18nKey) {
                    // create unexistent values
                    if (!$messageCatalogue->has($i18nKey, $filename)) {
                        $defaultValue = self::$untranslatedChar . $i18nKey . self::$untranslatedChar;
                        $messageCatalogue->set($i18nKey, $defaultValue, $filename);
                    }
                }

                // call dumper to dump content for each domain
                $dumper->dump($messageCatalogue, array('path' => $i18nPath));
            }
        }
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
     * Add keys used in a bundle (but not defined in translation files) to messages file
     * @param string $bundlePath
     */
    public function fillUpUndefinedKeys($bundlePath)
    {
        // get all keys used in a bundle
        $i18nKeys = $this->extractI18nKeys($bundlePath);

        // get loader
        $loader = $this->loaderFactory('yml');
//         $loader->load($resource, $this->locales);
        $i18nDefinedKeys = array();
    }

    /**
     * Extract all i18n keys used in a bundle
     * @param string $bundlePath
     *
     * @return Ambigous <\Bap\Bundle\ToolsBundle\Service\multiple:string, multitype:>
     */
    protected function extractI18nKeys($bundlePath)
    {
        $extractor = new ExtractorService();
        $i18nKeys = $extractor->extractI18nKeys($bundlePath);

        return $i18nKeys;
    }

    public function detectUselessKeys()
    {

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
     * Factory method ?! to get File Loader object in corresponding format
     * @param string $format
     *
     * @return \Bap\Bundle\ToolsBundle\Service\YamlFileLoader
     * @throws \Exception
     */
    protected function loaderFactory($format)
    {
        if ($format === 'yml') {
            return new YamlFileLoader();
        } else {
            throw new \Exception('not yet implemented');
        }
    }

    /**
     * Factory method ?! to get file dumper object in corresponding format
     * @param string $format
     *
     * @return \Bap\Bundle\ToolsBundle\Service\YamlFileDumper
     * @throws \Exception
     */
    protected function dumperFactory($format)
    {
        if ($format === 'yml') {
            return new YamlFileDumper();
        } else {
            throw new \Exception('not yet implemented');
        }
    }

    /**
     * Extract bundles path from source path
     *
     * @return multitype:string
     */
    protected function extractBundles()
    {
        $extractor = new ExtractorService();
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
        $extractor = new ExtractorService();
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
        $extractor = new ExtractorService();
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
        $extractor = new ExtractorService();
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