<?php
namespace Bap\Bundle\ToolsBundle\Command;

use Bap\Bundle\ToolsBundle\Service\StringsToLocalizeExtractorService;

use Symfony\Component\Translation\Loader\YamlFileLoader;

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

        // TODO : must be somewhere else
        $defaultLocale = $this->getContainer()->getParameter('locale');

        $sourcePath = $this->getSourceDirectory();


        $service = new StringsToLocalizeExtractorService();
        $uselessKeys = $service->extractStringsToLocalize($sourcePath);

        var_dump($uselessKeys);
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