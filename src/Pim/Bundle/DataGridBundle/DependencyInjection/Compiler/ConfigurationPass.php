<?php

namespace Pim\Bundle\DataGridBundle\DependencyInjection\Compiler;

use Oro\Bundle\DataGridBundle\DependencyInjection\CompilerPass\ConfigurationPass as OroConfigurationPass;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
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
        $this->registerConfigFiles($container);
    }

    /**
     * Collect datagrid configurations files located in the datagrid directory
     * and pass them to the configuration provider.
     *
     * @param ContainerBuilder $container
     */
    protected function registerConfigFiles(ContainerBuilder $container)
    {
        if ($container->hasDefinition(OroConfigurationPass::PROVIDER_SERVICE_ID)) {
            $config = [];

            foreach ($container->getParameter('kernel.bundles') as $bundle) {
                $reflection = new \ReflectionClass($bundle);
                $directory = sprintf(
                    '%s/Resources/config/%s',
                    dirname($reflection->getFilename()),
                    OroConfigurationPass::ROOT_PARAMETER
                );

                if (is_dir($directory)) {
                    $config = array_merge_recursive($config, $this->fetchBundleConfiguration($container, $directory));
                }
            }

            $configProviderDef = $container->getDefinition(OroConfigurationPass::PROVIDER_SERVICE_ID);
            $oroConfig = $configProviderDef->getArgument(0);
            // replace oro config (coming from datagrid.yml files)
            $configProviderDef->replaceArgument(0, array_merge_recursive($config, $oroConfig));
        }
    }

    /**
     * Get all the datagrid configurations for a bundle.
     *
     * @param ContainerBuilder $container the container
     * @param string           $directory the path of the bundle
     *
     * @return array
     */
    protected function fetchBundleConfiguration(ContainerBuilder $container, $directory)
    {
        $config = [];
        $files = new Finder();
        $files->files()->in($directory)->name('*.yml');

        foreach($files as $file) {
            $gridConfig = Yaml::parse($file->getPathName());
            if (isset($gridConfig[OroConfigurationPass::ROOT_PARAMETER]) &&
                is_array($gridConfig[OroConfigurationPass::ROOT_PARAMETER])
            ) {
                $config = array_merge_recursive($config, $gridConfig[OroConfigurationPass::ROOT_PARAMETER]);
            }
            $container->addResource(new FileResource($file));
        }

        return $config;
    }
}
