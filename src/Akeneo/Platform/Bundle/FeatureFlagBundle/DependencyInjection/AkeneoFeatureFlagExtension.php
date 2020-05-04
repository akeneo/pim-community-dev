<?php

namespace Akeneo\Platform\Bundle\FeatureFlagBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AkeneoFeatureFlagExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $this->registerFlags($configs, $container);
    }

    private function registerFlags(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $registry = $container->getDefinition('akeneo.feature_flag.service.registry');
        foreach ($config['feature_flags'] as $item) {
            $reference = new Reference($item['service']);
            $registry->addMethodCall('add', [$item['feature'], $reference]);
        }
    }
}
