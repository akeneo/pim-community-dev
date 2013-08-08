<?php

namespace Oro\Bundle\EntityConfigBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

use Oro\Bundle\EntityConfigBundle\Exception\RuntimeException;

class OroEntityConfigExtension extends Extension
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
        $loader->load('metadata.yml');
        $loader->load('datagrid.yml');
        $loader->load('form_type.yml');
    }

    /**
     * @param  ContainerBuilder $container
     * @param                   $config
     * @throws RuntimeException
     */
    protected function configCache(ContainerBuilder $container, $config)
    {
        $cacheDir = $container->getParameterBag()->resolveValue($config['cache_dir']);

        $fs = new Filesystem();
        $fs->remove($cacheDir);

        if (!is_dir($cacheDir)) {
            if (false === @mkdir($cacheDir, 0777, true)) {
                throw new RuntimeException(sprintf('Could not create cache directory "%s".', $cacheDir));
            }
        }
        $container->setParameter('oro_entity_config.cache_dir', $cacheDir);

        $configCacheDir = $cacheDir . '/config';
        if (!is_dir($configCacheDir)) {
            if (false === @mkdir($configCacheDir, 0777, true)) {
                throw new RuntimeException(sprintf('Could not create config cache directory "%s".', $configCacheDir));
            }
        }
        $container->setParameter('oro_entity_config.cache_dir.config', $configCacheDir);

        $annotationCacheDir = $cacheDir . '/annotation';
        if (!is_dir($annotationCacheDir)) {
            if (false === @mkdir($annotationCacheDir, 0777, true)) {
                throw new RuntimeException(sprintf('Could not create annotation cache directory "%s".', $annotationCacheDir));
            }
        }
        $container->setParameter('oro_entity_config.cache_dir.annotation', $annotationCacheDir);
    }
}
