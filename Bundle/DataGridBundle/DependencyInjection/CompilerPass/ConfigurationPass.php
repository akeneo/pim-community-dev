<?php

namespace Oro\Bundle\DataGridBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class ConfigurationPass implements CompilerPassInterface
{
    const MANAGER_SERVICE_ID = 'oro_datagrid.datagrid.manager';
    const BUILDER_SERVICE_ID = 'oro_datagrid.datagrid.builder';

    const SOURCE_TAG_NAME    = 'oro_datagrid.datasource';
    const EXTENSION_TAG_NAME = 'oro_datagrid.extension';

    const CONFIG_FILE_NAME = 'datagrid.yml';
    const ROOT_PARAMETER   = 'datagrid';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $config = [];

        /**
         * Collect and pass datagrid configurations to manager
         */
        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()) . '/Resources/config/' . self::CONFIG_FILE_NAME)) {
                $bundleConfig = Yaml::parse(realpath($file));

                if (isset($bundleConfig[self::ROOT_PARAMETER]) && is_array($bundleConfig[self::ROOT_PARAMETER])) {
                    $config = array_merge_recursive($config, $bundleConfig[self::ROOT_PARAMETER]);
                }
            }
        }

        $manager = $container->getDefinition(self::MANAGER_SERVICE_ID);
        if ($manager) {
            $manager->replaceArgument(0, $config);
        }

        /**
         * Find and add available datasources and extensions to grid builder
         */
        $builder = $container->getDefinition(self::BUILDER_SERVICE_ID);
        if ($builder) {
            $sources = $container->findTaggedServiceIds(self::SOURCE_TAG_NAME);
            foreach ($sources as $serviceId => $tags) {
                $tagAttrs = reset($tags);
                $builder->addMethodCall('registerDatasource', [$tagAttrs['type'], new Reference($serviceId)]);
            }

            $extensions = $container->findTaggedServiceIds(self::EXTENSION_TAG_NAME);
            foreach ($extensions as $serviceId => $tags) {
                $builder->addMethodCall('registerExtension', [new Reference($serviceId)]);
            }
        }
    }
}
