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
 * Registers every data hydrators in the dedicated registry
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RegisterValueDataHydratorPass implements CompilerPassInterface
{
    private const VALUE_DATA_HYDRATOR_REGISTRY = 'akeneo_assetmanager.infrastructure.persistence.asset.hydrator.data_hydrator_registry';
    private const VALUE_DATA_HYDRATOR_TAG = 'akeneo_assetmanager.data_hydrator';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $registry = $container->getDefinition(self::VALUE_DATA_HYDRATOR_REGISTRY);
        $attributeHydrators = $container->findTaggedServiceIds(self::VALUE_DATA_HYDRATOR_TAG);

        foreach (array_keys($attributeHydrators) as $attributeHydratorId) {
            $registry->addMethodCall('register', [new Reference($attributeHydratorId)]);
        }
    }
}
