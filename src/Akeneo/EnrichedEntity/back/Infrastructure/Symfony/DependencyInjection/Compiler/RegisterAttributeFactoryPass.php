<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers all attribute factories
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RegisterAttributeFactoryPass implements CompilerPassInterface
{
    private const ATTRIBUTE_FACTORY_REGISTRY = 'akeneo_referenceentity.application.registry.attribute_factory';
    private const ATTRIBUTE_FACTORY_TAG = 'akeneo_referenceentity.attribute_factory';

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
