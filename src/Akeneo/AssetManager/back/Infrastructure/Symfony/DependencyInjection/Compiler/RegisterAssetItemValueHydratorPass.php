<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers every AssetItem value hydrators in the dedicated registry
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class RegisterAssetItemValueHydratorPass implements CompilerPassInterface
{
    private const VALUE_HYDRATOR_REGISTRY = 'akeneo_assetmanager.infrastructure.persistence.asset.hydrator.asset_item_value_hydrator_registry';
    private const VALUE_HYDRATOR_TAG = 'akeneo_assetmanager.asset_item_value_hydrator';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(self::VALUE_HYDRATOR_REGISTRY);
        $valueHydrators = $container->findTaggedServiceIds(self::VALUE_HYDRATOR_TAG);

        foreach (array_keys($valueHydrators) as $valueHydratorId) {
            $registry->addMethodCall('register', [new Reference($valueHydratorId)]);
        }
    }
}
