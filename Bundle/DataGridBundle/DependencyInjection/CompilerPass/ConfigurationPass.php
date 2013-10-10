<?php

namespace Oro\Bundle\DataGridBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class ConfigurationPass implements CompilerPassInterface
{
    const MANAGER_SERVICE_ID = 'oro_grid.datagrid.manager';
    const BUILDER_SERVICE_ID = 'oro_grid.datagrid.builder';

    const SOURCE_TAG_NAME     = 'oro_grid.datasource';
    const EXTGENSION_TAG_NAME = 'oro_grid.extension';

    const CONFIG_FILE_NAME = 'datagrid.yml';
    const ROOT_PARAMETER   = 'datagrid';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $config = array();

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
         * Find and add available datasources to grid builder
         */
        $builder = $container->getDefinition(self::BUILDER_SERVICE_ID);
        if ($builder) {
            $sources = $container->findTaggedServiceIds(self::SOURCE_TAG_NAME);
            foreach ($sources as $serviceId => $tags) {
                $tagAttrs = reset($tags);
                $builder->addMethodCall('addDatasource', array($tagAttrs['type'], new Reference($serviceId)));
            }

            $extensions = $container->findTaggedServiceIds(self::EXTGENSION_TAG_NAME);
            foreach ($extensions as $serviceId => $tags) {
                $builder->addMethodCall('addExtension', array(new Reference($serviceId)));
            }
        }
    }
}
