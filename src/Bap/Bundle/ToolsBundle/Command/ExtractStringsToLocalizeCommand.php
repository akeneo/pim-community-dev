<?php
namespace Bap\Bundle\ToolsBundle\Command;

use Symfony\Component\DependencyInjection\Dumper\YamlDumper;

use Symfony\Component\Translation\MessageCatalogue;

use Symfony\Component\Yaml\Dumper;

use Symfony\Component\Yaml\Yaml;

use Symfony\Component\Translation\Dumper\YamlFileDumper;

use Symfony\Component\Filesystem\Filesystem;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Finder\Finder;

/**
 * Aims to extract strings to localize in the application
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO : block for production environment
 * TODO : option to define translation format (yml, xliff or php)
 *
 */
class ExtractStringsToLocalizeCommand extends ContainerAwareCommand
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
    protected static $bundleNamePattern = '/^(.+)Bundle$/';

    // TODO : add sauts de lignes + récupération key saut de ligne pattern : (?:\n?.*)
    // TODO : voir si les espaces sont obligatoires en twig
    protected static $transPatterns = array(
        '?:->trans\((?:\n?.*)(?:\'|\")(.+)(?:\'|\")',        // '->trans\((\'|\")(\w|(\w|\w.)+)(\'|\")',
//         '->transChoice\(',
//         '{% trans %}(\w+){% endtrans %}', //TODO : must be deleted
//         '{% trans with (\w+)%}(\w+){% endtrans %}',
//         '{% transchoice (\w+)%}(\w+){% endtranschoice %}',
//         '{{ (\w+) | trans(.*) }}',
//         '{{ (\w+) | transchoice(.*) }}'
    );

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('tools:extractStringsToLocalize')
             ->setDescription('Extract strings to localize in the application');
    }

    /**
     * Extract all locales used in source code
     * @return multitype:string
     */
    protected function extractLocales()
    {
        $locales = array();

        // get all translate files
        $finder = Finder::create();
        $finder->files()->name(self::$i18nFilesPattern)->in($this->getSourceDirectory());

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
     * Extract all bundles name existing in project
     * @return multitype:string
     */
    protected function extractBundles()
    {
        $bundlesDirectories = array();

        // get all bundles directories
        $finder = Finder::create();
        $finder->directories()->name(self::$bundleNamePattern)->in($this->getSourceDirectory());
        foreach ($finder as $directory) {
            $bundlesDirectories[] = $directory->getRealPath();
        }

        return $bundlesDirectories;
    }

    /**
     * Extract strings to translate
     * @param string $bundlePath
     *
     * @return multiple:string
     */
    protected function extractI18nStrings($bundlePath)
    {
        $i18nKeys = array();
        $i18nPattern = '/('. implode('|', self::$transPatterns) .')/'; // TODO : must be define only one time

        $finder = Finder::create();
        $finder->files()->contains($i18nPattern)->in($bundlePath);

        foreach ($finder as $file) {
            if (preg_match_all($i18nPattern, $file->getContents(), $matches)) {
                $i18nKeys = array_merge($i18nKeys, $matches[1]);
            }
        }

        return array_unique($i18nKeys);
    }

    /**
     * Extract filenames
     * @param string $bundlePath
     *
     * @return multitype:string
     */
    protected function extractFilenames($bundlePath)
    {
        $files = array();

        $finderFiles = Finder::create();
        $finderFiles->files()->name(self::$i18nFilesPattern)->in($bundlePath);

        foreach ($finderFiles as $file) {
            if (preg_match(self::$i18nFilesPattern, $file->getFileName(), $matches)) {
                if (!empty($matches[1])) {
                    $files[] = $matches[1];
                }
            }
        }

        return array_unique($files);
    }

    /**
     * get i18n value from key
     * @param string $i18nKey
     *
     * @return string
     */
    protected function getI18nValue($i18nKey)
    {
        return $this->getContainer()->get('translator')->trans($i18nKey);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // TODO : must be somewhere else
        $defaultLocale = $this->getContainer()->getParameter('locale');

        $fileSystem = new Filesystem();

        // 1. extract locales
        $locales = $this->extractLocales();

        // 2. get all bundles directories
        $bundles = $this->extractBundles();

        foreach ($bundles as $bundle) {
            echo "\n-------------------------------------\n";
            echo $bundle ."\n";
            $i18nPath = $bundle .'/Resources/translations/';

            // if directory not exists, no translation files to copy TODO : make a forced copy ?
            if (!is_dir($i18nPath)) {
                continue;
            }

            // 3. get all strings to translate by bundles then exchanges keys and associated values
            $i18nStrings = $this->extractI18nStrings($bundle);
            var_dump($i18nStrings);
//             $i18nKeys = array_flip($i18nStrings);

            // 4. get all files
            $filenames = $this->extractFilenames($bundle);
//             var_dump($filenames);

//             $i18nContent = $this->extractI18nContent($locales, $filenames, $i18nStrings);

            $i18nContent = array();

            foreach ($locales as $locale) {
                echo "\tLocale : ". $locale ."\n";
                // change locale in session to get translated value
                $this->getContainer()->get('translator')->setLocale($locale);
                foreach ($filenames as $filename) {
                    // 5. create unexistent files
                    $i18nFile = $i18nPath . $filename .'.'. $locale .'.yml'; // TODO : extension must be a parameter
                    if (!file_exists($i18nFile)) {
//                         $fileSystem->touch($i18nFile);
                        echo "\t\t--> create file ". $i18nFile ."\n";
                    }
                }

                $filename = $i18nPath .'messages.'. $locale .'.yml'; // TODO : extension must be a parameter

                // 6. prepare array data [key][locale] = value
                foreach ($i18nStrings as $i18nKey) {
                    $i18nValue = $this->getContainer()->get('translator')->trans($i18nKey);
                    if ($locale !== $defaultLocale && $i18nKey === $i18nValue) {
                        $i18nValue = '##'. $i18nValue .'##';
                    }
                    echo "\t\t\t". $i18nKey ." -> ". $i18nValue ."\n";

                    $i18nContent[$i18nKey][$locale] = $i18nValue;
                }

                // 7. dump content in files

                $dumper = new \Symfony\Component\Translation\Dumper\YamlFileDumper();



//                 $dumper = new YamlFileDumper();
//                 $ymlContent = new MessageCatalogue($locale);
                $ymlContent = array('ma cle' => 'ma valeur');






//                 new MessageCatalogue();


//                 new YamlDumper($container)

//                 $dumper = new Dumper();
//                 $yaml = $dumper->dump($ymlContent);
//                 foreach ($i18nContent as $key => $value) {
//                     $ymlContent[$key] = $value[$locale];
//                 }

//                 $yaml = $dumper->dump($ymlContent);
//                 $yaml = Yaml::dump($ymlContent);
//                 $yaml = $dumper->dump($ymlContent);
//                 var_dump($yaml);
//                 file_put_contents($i18nPath .'content.'. $locale .'.yml', $yaml);



//                 $array = array(
//                         'foo' => 'bar',
//                         'bar' => array('foo' => 'bar', 'bar' => 'baz')
//                 );

//                 $dumper = new Dumper();

//                 $yaml = $dumper->dump($array);

//                 file_put_contents('/tmp/file.yml', $yaml);

            }

            // 7. dump contents in files
//             $this->dumpContent($i18nContent, $i18nPath);


        }
    }

    /**
     * Dump content in a file
     * @param multitype:array $content  associative array with all i18n content
     * @param string          $i18nPath path to translation files
     */
    protected function dumpContent($content, $i18nPath)
    {

    }

    protected function getRootDirectory()
    {
        return $this->getContainer()->get('kernel')->getRootDir();
    }

    protected function getSourceDirectory()
    {
        return $this->getRootDirectory() .'/../src';
    }
}