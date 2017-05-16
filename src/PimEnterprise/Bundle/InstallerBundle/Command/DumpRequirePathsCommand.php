<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\InstallerBundle\Command;

use Pim\Bundle\UserBundle\Doctrine\ORM\Repository\GroupRepository;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Revoke ALL group on category accesses.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class DumpRequirePathsCommand extends ContainerAwareCommand
{

    const MAIN_CONFIG_FILE_NAME = 'js/require-config.js';
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pimee:installer:dump-require-paths')
            ->setDescription('Dump the paths for all the requirejs.yml files for each bundle');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Generating require.js main config');

        $webRoot = $this->getContainer()->getParameter('oro_require_js.web_root');
        $config = $this->getContainer()->getParameter('oro_require_js');
        // $configProvider = $this->getContainer()->get('oro_requirejs_config_provider');
        $mainConfigContent = json_encode($this->collectConfigPaths(), JSON_UNESCAPED_SLASHES);

        $mainConfigContent = 'module.exports = ' . $mainConfigContent;
        $mainConfigContent = str_replace(',', ",\n", $mainConfigContent);
        $mainConfigFilePath = $webRoot . DIRECTORY_SEPARATOR . self::MAIN_CONFIG_FILE_NAME;
        if (false === @file_put_contents($mainConfigFilePath, $mainConfigContent)) {
            throw new \RuntimeException('Unable to write file ' . $mainConfigFilePath);
        }
    }

    public function collectConfigPaths()
    {
        $kernel = $this->getApplication()->getKernel();
        $bundles = $this->getContainer()->getParameter('kernel.bundles');
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir');

        $paths = array();

        // Tell it to output the paths relative to where you are running the command from
        foreach ($bundles as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            $fileName = dirname($reflection->getFilename()) . '/Resources/config/requirejs.yml';
            $paths[] = $fileName;
        }

        return $paths;
    }
}
