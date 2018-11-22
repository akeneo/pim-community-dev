<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\DependencyInjection\Compiler;

use Akeneo\Asset\Component\Factory\DefaultAssetTree;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ConfigureUserServicesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $userUpdater = $container->getDefinition('pim_user.updater.user');
        $userUpdater->addArgument('asset_delay_reminder');
        $userUpdater->addArgument('default_asset_tree');

        $userNormalizer = $container->getDefinition('pim_user.normalizer.user');
        $userNormalizer->addArgument('asset_delay_reminder');
        $userNormalizer->addArgument('default_asset_tree');

        $userFactory = $container->getDefinition('pim_user.factory.user');
        $defaultAssetTree = $container->getDefinition(DefaultAssetTree::class);
        $defaultAssetDelayReminder = $container->getDefinition('pimee_asset.factory.user.default_asset_delay_reminder');

        $userFactory->addArgument($defaultAssetTree);
        $userFactory->addArgument($defaultAssetDelayReminder);
    }
}
