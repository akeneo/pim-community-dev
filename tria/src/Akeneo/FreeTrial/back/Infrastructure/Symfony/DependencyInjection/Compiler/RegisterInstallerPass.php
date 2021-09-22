<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterInstallerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('akeneo.free_trial.installer_registry')) {
            return;
        }

        $registry = $container->getDefinition('akeneo.free_trial.installer_registry');

        $taggedServices = $container->findTaggedServiceIds('free_trial.fixture_installer');

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $registry->addMethodCall('addInstaller', [new Reference($id), $attributes['alias']]);
            }
        }
    }
}
