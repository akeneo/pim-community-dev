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
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RegisterEditAssetValueCommandFactoryPass implements CompilerPassInterface
{
    private const ASSET_VALUE_COMMAND_FACTORY_REGISTRY = 'akeneo_assetmanager.application.registry.asset.edit_asset_value_command_factory_registry';
    private const ASSET_VALUE_COMMAND_FACTORY_TAG = 'akeneo_assetmanager.edit_asset_value_command_factory';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $registry = $container->getDefinition(self::ASSET_VALUE_COMMAND_FACTORY_REGISTRY);
        $assetValueFactories = $container->findTaggedServiceIds(self::ASSET_VALUE_COMMAND_FACTORY_TAG);

        foreach (array_keys($assetValueFactories) as $assetValueFactoryId) {
            $registry->addMethodCall('register', [new Reference($assetValueFactoryId)]);
        }
    }
}
