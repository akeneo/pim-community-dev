<?php

namespace Oro\Bundle\DataGridBundle\DependencyInjection\CompilerPass;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class ConfigurationPass implements CompilerPassInterface
{
    const CONFIG_FILE_NAME = 'datagrid.yml';
    const ROOT_PARAMETER   = 'datagrid';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $config    = array();

        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()) . '/Resources/config/' . self::CONFIG_FILE_NAME)) {
                $bundleConfig = Yaml::parse(realpath($file));

                if (isset($bundleConfig[self::ROOT_PARAMETER]) && is_array($bundleConfig[self::ROOT_PARAMETER])) {
                    $config = array_merge_recursive($config, $bundleConfig[self::ROOT_PARAMETER]);
                }
            }
        }

        // @TODO add passing to builder service
        // @TODO add tree validation
    }
}
