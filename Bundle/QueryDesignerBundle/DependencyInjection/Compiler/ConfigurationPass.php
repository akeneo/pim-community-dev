<?php

namespace Oro\Bundle\QueryDesignerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

class ConfigurationPass implements CompilerPassInterface
{
    const MANAGER_SERVICE_ID = 'oro_querydesigner.querydesigner.manager';
    const CONFIG_FILE_NAME = 'query_designer.yml';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition(self::MANAGER_SERVICE_ID)) {
            $config = array();
            foreach ($container->getParameter('kernel.bundles') as $bundle) {
                $reflection = new \ReflectionClass($bundle);
                $file = dirname($reflection->getFilename()) . '/Resources/config/' . self::CONFIG_FILE_NAME;
                if (is_file($file)) {
                    $config = array_merge_recursive($config, Yaml::parse(realpath($file)));
                }
            }

            $managerDef = $container->getDefinition(self::MANAGER_SERVICE_ID);
            $managerDef->replaceArgument(0, $config);
        }
    }
}
