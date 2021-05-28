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
class RegisterAttributeUpdaterPass implements CompilerPassInterface
{
    private const ATTRIBUTE_FACTORY_REGISTRY = 'akeneo_assetmanager.application.edit_attribute.attribute_updater.attribute_updater_registry';
    private const ATTRIBUTE_FACTORY_TAG = 'akeneo_assetmanager.attribute_updater';
    private const DEFAULT_PRIORITY = 50;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(self::ATTRIBUTE_FACTORY_REGISTRY);
        $sortedUpdatersByPriority = $this->findAndSortTaggedServices($container);

        foreach ($sortedUpdatersByPriority as $attributeUpdaterId) {
            $registry->addMethodCall('register', [new Reference($attributeUpdaterId)]);
        }
    }

    private function findAndSortTaggedServices(ContainerBuilder $container): array
    {
        $attributeUpdaters = $container->findTaggedServiceIds(self::ATTRIBUTE_FACTORY_TAG);

        $sortedUpdatersByPriority = [];
        foreach ($attributeUpdaters as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $priority = isset($tag['priority']) ? $tag['priority'] : static::DEFAULT_PRIORITY;
                $sortedUpdatersByPriority[$priority][] = new Reference($serviceId);
            }
        }

        krsort($sortedUpdatersByPriority);

        return call_user_func_array('array_merge', $sortedUpdatersByPriority);
    }
}
