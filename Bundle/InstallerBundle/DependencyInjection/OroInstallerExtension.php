<?php

namespace Oro\Bundle\InstallerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class OroInstallerExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {
        $processor = new Processor();

        $config = $processor->processConfiguration(new Configuration(), $config);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        foreach ($config['classes'] as $model => $classes) {
            foreach ($classes as $service => $class) {
                $container->setParameter(sprintf('oro.%s.%s.class', $service, $model), $class);
            }
        }

        $loader->load('services.xml');
    }
}
