<?php

namespace Oro\Bundle\ConfigBundle\DependencyInjection\Compiler;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

use Oro\Bundle\ConfigBundle\DependencyInjection\SystemConfiguration\ProcessorDecorator;

class SystemConfigurationPass implements CompilerPassInterface
{
    const CONFIG_FILE_NAME  = 'system_configuration.yml';
    const CONFIG_PARAM_NAME = 'oro_config.system_configuration.config_data';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $config = array();
        $processor = new ProcessorDecorator();

        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()) . '/Resources/config/' . self::CONFIG_FILE_NAME)) {
                $bundleConfig = Yaml::parse(realpath($file));

                $config = $processor->merge($config, $bundleConfig);
            }
        }

        if (!empty($config)) {
            /**
             * @TODO config data should be added via setter to services needed it
             */
            $container->setParameter(self::CONFIG_PARAM_NAME, $processor->process($config));
        }
    }
}
