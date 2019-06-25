<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
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
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class RegisterPreviewGeneratorPass implements CompilerPassInterface
{
    private const PREVIEW_GENERATOR_REGISTRY = 'akeneo_assetmanager.infrastructure.registry.preview_generator';
    private const PREVIEW_GENERATOR_TAG = 'akeneo_assetmanager.preview_generator';

    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(self::PREVIEW_GENERATOR_REGISTRY);
        $previewGenerators = $container->findTaggedServiceIds(self::PREVIEW_GENERATOR_TAG);

        foreach (array_keys($previewGenerators) as $previewGeneratorId) {
            $registry->addMethodCall('register', [new Reference($previewGeneratorId)]);
        }
    }
}
