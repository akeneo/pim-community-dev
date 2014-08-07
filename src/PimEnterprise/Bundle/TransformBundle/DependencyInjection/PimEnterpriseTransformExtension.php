<?php

namespace PimEnterprise\Bundle\TransformBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;

/**
 * EE Transform bundle extension
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PimEnterpriseTransformExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->loadSerializerConfig($configs, $container);
    }

    /**
     * Load serializer related configuration
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    protected function loadSerializerConfig(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/serializer'));
        $loader->load('structured.yml');
    }
}
