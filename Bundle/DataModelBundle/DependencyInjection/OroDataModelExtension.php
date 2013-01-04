<?php

namespace Oro\Bundle\DataModelBundle\DependencyInjection;

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
class OroDataModelExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        // load flexible entities configuration
        if (count($config['entities_config'])) {
            $this->remapParameters(
                $config,
                $container,
                array(
                    'entities_config' => 'oro_flexibleentity.entities_config'
                )
            );
        } else {
            $entitiesConfig = array();
            foreach ($container->getParameter('kernel.bundles') as $bundle) {
                $reflection = new \ReflectionClass($bundle);
                if (is_file($file = dirname($reflection->getFilename()).'/Resources/config/flexibleentity.yml')) {
                    $entitiesConfig += Yaml::parse(realpath($file));
                }
                $container->setParameter('oro_flexibleentity.entities_config', $entitiesConfig);
            }
        }

        $loader->load('services.yml');
    }
}
