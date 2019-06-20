<?php
declare(strict_types=1);

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
class RegisterValueUpdaterPass implements CompilerPassInterface
{
    private const ASSET_VALUE_UPDATER_REGISTRY = 'akeneo_assetmanager.application.registry.edit_asset.asset_value_updater.asset_value_updater_registry';
    private const ASSET_VALUE_UPDATER_TAG = 'akeneo_assetmanager.asset_value_updater';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $registry = $container->getDefinition(self::ASSET_VALUE_UPDATER_REGISTRY);
        $assetValueUpdaters = $container->findTaggedServiceIds(self::ASSET_VALUE_UPDATER_TAG);

        foreach (array_keys($assetValueUpdaters) as $assetValueUpdaterId) {
            $registry->addMethodCall('register', [new Reference($assetValueUpdaterId)]);
        }
    }
}
