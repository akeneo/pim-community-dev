<?php

namespace Oro\Bundle\ConfigBundle\DependencyInjection\Compiler;

use Oro\Bundle\ConfigBundle\DependencyInjection\SystemConfiguration\ProcessorDecorator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

class SystemConfigurationPass implements CompilerPassInterface
{
    const CONFIG_FILE_NAME = 'system_configuration.yml';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $config = [];
        $processor = new ProcessorDecorator();

        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()) . '/Resources/config/' . self::CONFIG_FILE_NAME)) {
                $bundleConfig = Yaml::parse(file_get_contents(realpath($file)));

                $config = $processor->merge($config, $bundleConfig);
            }
        }
    }
}
