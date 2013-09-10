<?php

namespace Oro\Bundle\EntityExtendBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

use Oro\Bundle\EntityExtendBundle\Exception\RuntimeException;

/**
 * This is the class that loads and manages your bundle configuration
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OroEntityExtendExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $this->configBackend($container, $config);

        $fileLocator = new FileLocator(__DIR__ . '/../Resources/config');
        $loader      = new Loader\YamlFileLoader($container, $fileLocator);
        $loader->load('services.yml');
    }

    protected function configBackend(ContainerBuilder $container, $config)
    {
        $backend = $container->getParameterBag()->resolveValue($config['backend']);
        $path    = $container->getParameterBag()->resolveValue($config['backup']);

        $container->setParameter('oro_entity_extend.backend', $backend);
        $container->setParameter('oro_entity_extend.backup', $path);

        // for DoctrineOrmMappingsPass end BackendCompilerPass. Detect with backend should be mapped and loaded
        $container->setParameter('oro_entity_extend.backend.' . strtolower($backend), true);
    }
}
