<?php

namespace Oro\Bundle\EntityConfigBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

class OroEntityConfigExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $fileLocator = new FileLocator(__DIR__ . '/../Resources/config');
        $loader      = new Loader\YamlFileLoader($container, $fileLocator);
        $loader->load('services.yml');
        $loader->load('metadata.yml');
        $loader->load('form_type.yml');
    }
}
