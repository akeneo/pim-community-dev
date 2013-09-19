<?php

namespace Oro\Bundle\EntityBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

use Oro\Bundle\EntityBundle\Exception\RuntimeException;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OroEntityExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->configCache($container, $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('orm.yml');
        $loader->load('ownership.yml');
        $loader->load('form_type.yml');
        $loader->load('datagrid.yml');
        $loader->load('services.yml');
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
        $container->setParameter('oro_entity.cache_dir', $cacheDir);

        $auditCacheDir = $cacheDir . '/audit';
        if (!is_dir($auditCacheDir)) {
            if (false === @mkdir($auditCacheDir, 0777, true)) {
                throw new RuntimeException(sprintf('Could not create "audit" cache directory "%s".', $auditCacheDir));
            }
        }
        $container->setParameter('oro_entity.cache_dir.audit', $auditCacheDir);
    }
}
