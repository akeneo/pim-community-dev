<?php

namespace Oro\Bundle\PimDataGridBundle\DependencyInjection\Compiler;

use Oro\Bundle\DataGridBundle\DependencyInjection\CompilerPass\ConfigurationPass as OroConfigurationPass;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

/**
 * Load datagrid configuration files
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigurationPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->registerDatagridFiles($container);
    }

    /**
     * Collect datagrid configurations files located in the datagrid directory
     * and pass them to the configuration provider.
     *
     * @param ContainerBuilder $container
     */
    protected function registerDatagridFiles(ContainerBuilder $container)
    {
        if ($container->hasDefinition(OroConfigurationPass::PROVIDER_SERVICE_ID)) {
            $config = [];
            $files = $this->listDatagridFiles($container);

            foreach ($files as $file) {
                $gridConfig = Yaml::parse(file_get_contents($file->getPathName()));
                if (isset($gridConfig[OroConfigurationPass::ROOT_PARAMETER]) &&
                    is_array($gridConfig[OroConfigurationPass::ROOT_PARAMETER])
                ) {
                    $config = array_merge_recursive($config, $gridConfig[OroConfigurationPass::ROOT_PARAMETER]);
                }
                $container->addResource(new FileResource($file->getPathName()));
            }

            $configProviderDef = $container->getDefinition(OroConfigurationPass::PROVIDER_SERVICE_ID);
            $oroConfig = $configProviderDef->getArgument(0);
            // replace oro config (coming from datagrid.yml files)
            $configProviderDef->replaceArgument(0, array_merge_recursive($config, $oroConfig));
        }
    }

    /**
     * Get all the datagrid configuration files registered in the Resources/datagrid/ directories.
     *
     * @param ContainerBuilder $container
     *
     * @return SplFileInfo[] array the files (key: name of the file)
     */
    protected function listDatagridFiles(ContainerBuilder $container)
    {
        $files = [];

        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            $directory = sprintf(
                '%s/Resources/config/%s',
                dirname($reflection->getFilename()),
                OroConfigurationPass::ROOT_PARAMETER
            );

            if (is_dir($directory)) {
                // using array_merge allow to override a file in a child bundle
                $files = array_merge($files, $this->listDatagridFilesInDirectory($directory));
            }
        }

        return $files;
    }

    /**
     * Get the list of datagrid files in a given directory.
     *
     * @param string $directory
     *
     * @return SplFileInfo[] array the files (key: name of the file)
     */
    protected function listDatagridFilesInDirectory($directory)
    {
        $files = [];
        $finder = new Finder();
        $finder->files()->in($directory)->name('*.yml');

        foreach ($finder as $file) {
            $files[$file->getRelativePathName()] = $file;
        }

        return $files;
    }
}
