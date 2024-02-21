<?php

namespace Oro\Bundle\DataGridBundle\DependencyInjection\CompilerPass;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Yaml\Yaml;

class ConfigurationPass implements CompilerPassInterface
{
    const BUILDER_SERVICE_ID = 'oro_datagrid.datagrid.builder';
    const PROVIDER_SERVICE_ID = 'oro_datagrid.configuration.provider';
    const CHAIN_PROVIDER_SERVICE_ID = 'oro_datagrid.configuration.provider.chain';

    const SOURCE_TAG_NAME = 'oro_datagrid.datasource';
    const EXTENSION_TAG_NAME = 'oro_datagrid.extension';
    const PROVIDER_TAG_NAME = 'oro_datagrid.configuration.provider';

    const CONFIG_FILE_NAME = 'datagrid.yml';
    const ROOT_PARAMETER = 'datagrid';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->registerConfigFiles($container);
        $this->registerConfigProviders($container);
        $this->registerDataSources($container);
    }

    /**
     * Collect datagrid configurations files and pass them to the configuration provider
     *
     * @param ContainerBuilder $container
     */
    protected function registerConfigFiles(ContainerBuilder $container)
    {
        if ($container->hasDefinition(self::PROVIDER_SERVICE_ID)) {
            $config = [];
            foreach ($container->getParameter('kernel.bundles') as $bundle) {
                $reflection = new \ReflectionClass($bundle);
                $file = dirname($reflection->getFilename()) . '/Resources/config/' . self::CONFIG_FILE_NAME;
                if (is_file($file)) {
                    $bundleConfig = Yaml::parse(file_get_contents(realpath($file)));
                    if (isset($bundleConfig[self::ROOT_PARAMETER]) && is_array($bundleConfig[self::ROOT_PARAMETER])) {
                        $config = array_merge_recursive($config, $bundleConfig[self::ROOT_PARAMETER]);
                    }
                    $container->addResource(new FileResource($file));
                }
            }

            $configProviderDef = $container->getDefinition(self::PROVIDER_SERVICE_ID);
            $configProviderDef->replaceArgument(0, $config);
        }
    }

    /**
     * Register all datagrid configuration providers
     *
     * @param ContainerBuilder $container
     */
    protected function registerConfigProviders(ContainerBuilder $container)
    {
        if ($container->hasDefinition(self::CHAIN_PROVIDER_SERVICE_ID)) {
            $providers = [];
            foreach ($container->findTaggedServiceIds(self::PROVIDER_TAG_NAME) as $id => $attributes) {
                $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
                $providers[$priority][] = new Reference($id);
            }
            if (!empty($providers)) {
                // sort by priority and flatten
                krsort($providers);
                $providers = call_user_func_array('array_merge', $providers);
                // add to chain provider
                $chainConfigProviderDef = $container->getDefinition(self::CHAIN_PROVIDER_SERVICE_ID);
                foreach ($providers as $provider) {
                    $chainConfigProviderDef->addMethodCall('addProvider', [$provider]);
                }
            }
        }
    }

    /**
     * Find and add available datasources and extensions to grid builder
     *
     * @param ContainerBuilder $container
     */
    protected function registerDataSources(ContainerBuilder $container)
    {
        if ($container->hasDefinition(self::BUILDER_SERVICE_ID)) {
            $builderDef = $container->getDefinition(self::BUILDER_SERVICE_ID);
            $sources = $container->findTaggedServiceIds(self::SOURCE_TAG_NAME);
            foreach ($sources as $serviceId => $tags) {
                $tagAttrs = reset($tags);
                $builderDef->addMethodCall('registerDatasource', [$tagAttrs['type'], new Reference($serviceId)]);
            }

            $extensions = $container->findTaggedServiceIds(self::EXTENSION_TAG_NAME);
            foreach ($extensions as $serviceId => $tags) {
                $builderDef->addMethodCall('registerExtension', [new Reference($serviceId)]);
            }
        }
    }
}
