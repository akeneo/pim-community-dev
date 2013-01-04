<?php

namespace Oro\Bundle\FlexibleEntityBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OroFlexibleEntityExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // retrieve each flexible entity config from bundles
        $entitiesConfig = array();
        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()).'/Resources/config/flexibleentity.yml')) {
                // merge entity configs
                if (empty($entitiesConfig)) {
                    $entitiesConfig = Yaml::parse(realpath($file));
                } else {
                    $entities = Yaml::parse(realpath($file));
                    foreach ($entities['entities_config'] as $entity => $entityConfig) {
                        $entitiesConfig['entities_config'][$entity]= $entityConfig;
                    }
                }
            }
        }
        $configs[]= $entitiesConfig;
        // process configurations to validate and merge
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        // load service
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        // set entities config
        $container->setParameter('oro_flexibleentity.entities_config', $config);
    }
}
