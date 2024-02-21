<?php

namespace Oro\Bundle\ConfigBundle\DependencyInjection\Compiler;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConfigPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $processor = new Processor();
        $settings = [];

        foreach ($container->getExtensions() as $name => $extension) {
            if (strpos($name, 'oro_') !== false || strpos($name, 'pim_') !== false) {
                if (!$config = $extension->getConfiguration([], $container)) {
                    continue;
                }

                $config = $processor->processConfiguration(
                    $config,
                    $container->getExtensionConfig($name)
                );

                if (isset($config['settings'])) {
                    $settings[$name] = $config['settings'];
                }
            }
        }

        $taggedServices = $container->findTaggedServiceIds('oro_config.manager');

        foreach ($taggedServices as $id => $attributes) {
            $container
                ->getDefinition($id)
                ->addArgument($settings);
        }
    }
}
