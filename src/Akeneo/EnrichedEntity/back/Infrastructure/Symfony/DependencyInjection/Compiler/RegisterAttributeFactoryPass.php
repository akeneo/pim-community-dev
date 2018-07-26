<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\Infrastructure\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register all action applier by priority
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class RegisterAttributeFactoryPass implements CompilerPassInterface
{
    private const ATTRIBUTE_FACTORY_REGISTRY = 'akeneo_enrichedentity.application.registry.attribute_factory';
    private const ATTRIBUTE_FACTORY_TAG = '';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(self::ATTRIBUTE_FACTORY_REGISTRY);
        $attributeFactories = $container->findTaggedServiceIds(self::ATTRIBUTE_FACTORY_TAG);

        foreach (array_keys($attributeFactories) as $attributeFactoryId) {
            $registry->addMethodCall('register', [new Reference($attributeFactoryId)]);
        }
    }
}
