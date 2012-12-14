<?php
namespace Bap\Bundle\ToolsBundle\Command;

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
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('tools:extractStringsToLocalize')
             ->setDescription('Extract strings to localize in the application');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $pattern = '/\.(\w{2}|\w{2}_\w{2})\.(xliff|yml|php)$/';

        // 1. get all bundles directories
        $bundlesDirectories = array();
        $finder = Finder::create();
        $finder->directories()->notName('/^Bundle$/')->name('*Bundle')->in($this->getSourceDirectory());
        foreach ($finder as $directory) {
            $bundlesDirectories[$directory->getRelativePathname()] = array();

            echo "\n------------------------------------\n";
            echo $directory->getRelativePathname() ."\n";


            $finderFiles = Finder::create();
            $finderFiles->files()->name($pattern)->in($this->getSourceDirectory());
            foreach ($finderFiles as $file) {
                echo $file->getFilename() ."\n";
            }

        }




        // 2. get locales
        $locales = array();
        $pattern = '/\.(\w{2}|\w{2}_\w{2})\.(xliff|yml|php)$/';

        // get all translate files
        $finder = Finder::create();
        $finder->files()->name($pattern)->in($this->getSourceDirectory());

        foreach ($finder as $file) {

            echo $file->getRelativePathname() ."\n";

            if (preg_match($pattern, $file->getFileName(), $matches)) {
                if (!empty($matches[1]) && !in_array($matches[1], $locales)) {
                    $locales[] = $matches[1];
                }
            }
        }



        // 3. build the same file tree
//         $baseDir = '/tmp'; // TODO : must be get from configuration

//         $baseDir .= '/src';
//         $fileSystem = new Filesystem();
//         foreach ($bundlesDirectories as $bundleDirectory) {
//             $dirPath = $baseDir .'/'. $bundleDirectory .'/Resources/translations';
//             $fileSystem->mkdir($dirPath);
//             foreach ($locales as $locale) {
//                 $fileSystem->touch($dirPath .'/messages.'. $locale .'.yml');
//             }
//         }
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