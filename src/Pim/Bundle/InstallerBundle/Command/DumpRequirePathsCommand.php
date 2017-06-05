<?php

namespace Pim\Bundle\InstallerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Dump a file called require-paths containing all requirejs.yml config files for reach registered bundle
 *
 * @author Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DumpRequirePathsCommand extends ContainerAwareCommand
{
    const MAIN_CONFIG_FILE_NAME = 'js/require-paths.js';
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:installer:dump-require-paths')
            ->setDescription('Dump the paths for all the requirejs.yml files for each bundle');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Generating require.js main config');

        $webRoot = 'web';

        $mainConfigContent = json_encode($this->collectConfigPaths(), JSON_UNESCAPED_SLASHES);

        $mainConfigContent = 'module.exports = ' . $mainConfigContent;
        $mainConfigContent = str_replace(',', ",\n", $mainConfigContent);
        $mainConfigFilePath = $webRoot . DIRECTORY_SEPARATOR . self::MAIN_CONFIG_FILE_NAME;
        if (false === file_put_contents($mainConfigFilePath, $mainConfigContent)) {
            throw new \RuntimeException('Unable to write file ' . $mainConfigFilePath);
        }
    }

    /**
     * Collect an array of requirejs.yml paths for each bundle
     * @return [Array] Array of paths
     */
    protected function collectConfigPaths()
    {
        $kernel = $this->getApplication()->getKernel();
        $bundles = $this->getContainer()->getParameter('kernel.bundles');
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir');

        $paths = array();

        foreach ($bundles as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            $fileName = dirname($reflection->getFilename()) . '/Resources/config/requirejs.yml';
            $paths[] = $fileName;
        }

        return $paths;
    }
}
