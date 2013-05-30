<?php

namespace Oro\Bundle\UIBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OroUIExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $this->positionsConfig($container);
    }

    /**
     * Add position mapping
     *
     * @param ContainerBuilder $container
     */
    private function positionsConfig(ContainerBuilder $container)
    {
        $positions = array();
        $bundles = $container->getParameter('kernel.bundles') ;
        foreach ($bundles as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()).'/Resources/config/positions.yml')) {
                $positions += Yaml::parse(realpath($file));
            }
        }
        $container->setParameter('oro_ui.positions', $positions);
    }
}
