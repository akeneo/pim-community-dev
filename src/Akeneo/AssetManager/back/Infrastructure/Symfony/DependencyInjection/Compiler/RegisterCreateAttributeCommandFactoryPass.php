<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers all "create command attribute" factories
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RegisterCreateAttributeCommandFactoryPass implements CompilerPassInterface
{
    private const ATTRIBUTE_FACTORY_REGISTRY = 'akeneo_assetmanager.application.registry.create_attribute_command_factory_registry';
    private const ATTRIBUTE_FACTORY_TAG = 'akeneo_assetmanager.create_attribute_command_factory';
    const DEFAULT_PRIORITY = 50;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(self::ATTRIBUTE_FACTORY_REGISTRY);
        $attributeFactories = $this->findAndSortTaggedServices($container);

        foreach ($attributeFactories as $attributeFactoryId) {
            $registry->addMethodCall('register', [new Reference($attributeFactoryId)]);
        }
    }

    private function findAndSortTaggedServices(ContainerBuilder $container): array
    {
        $attributeFactories = $container->findTaggedServiceIds(self::ATTRIBUTE_FACTORY_TAG);

        $sortedUpdatersByPriority = [];
        foreach ($attributeFactories as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $priority = isset($tag['priority']) ? $tag['priority'] : static::DEFAULT_PRIORITY;
                $sortedUpdatersByPriority[$priority][] = new Reference($serviceId);
            }
        }

        krsort($sortedUpdatersByPriority);

        return call_user_func_array('array_merge', $sortedUpdatersByPriority);
    }
}
