<?php

namespace Oro\Bundle\EntityExtendBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
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

        $this->configCache($container, $config);

        $fileLocator = new FileLocator(__DIR__ . '/../Resources/config');
        $loader      = new Loader\YamlFileLoader($container, $fileLocator);
        $loader->load('services.yml');
    }

    protected function configCache(ContainerBuilder $container, $config)
    {
        $cacheDir = $container->getParameterBag()->resolveValue($config['cache_dir']);

        $annotationCacheDir = $cacheDir . '/annotation';
        if (!is_dir($annotationCacheDir)) {
            if (false === @mkdir($annotationCacheDir, 0777, true)) {
                throw new RuntimeException(
                    sprintf('Could not create annotation cache directory "%s".', $annotationCacheDir)
                );
            }
        }
        $container->setParameter('oro_entity_extend.cache_dir.annotation', $annotationCacheDir);

    }
}
