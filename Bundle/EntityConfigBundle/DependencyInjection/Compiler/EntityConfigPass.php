<?php

namespace Oro\Bundle\EntityConfigBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Yaml\Yaml;

class EntityConfigPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $providerBagDefinition = $container->getDefinition('oro_entity_config.provider_bag');

        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()) . '/Resources/config/entity_config.yml')) {
                $bundleConfig = Yaml::parse(realpath($file));

                if (isset($bundleConfig['oro_entity_config']) && count($bundleConfig['oro_entity_config'])) {
                    foreach ($bundleConfig['oro_entity_config'] as $scope => $config) {
                        $provider = new Definition('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider');
                        $provider->setArguments(
                            array(
                                new Reference('oro_entity_config.config_manager'),
                                $scope,
                                $config
                            )
                        );

                        $container->setDefinition('oro_entity_config.provider.' . $scope, $provider);

                        $providerBagDefinition->addMethodCall('addProvider', array($provider));
                    }
                }
            }
        }
    }
}
