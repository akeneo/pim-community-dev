<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Symfony\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterPreviewGeneratorPass implements CompilerPassInterface
{
    private const PREVIEW_GENERATOR_REGISTRY = 'pim_category.infrastructure.registry.preview_generator';
    private const PREVIEW_GENERATOR_TAG = 'pim_category.preview_generator';

    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(self::PREVIEW_GENERATOR_REGISTRY);
        $previewGenerators = $container->findTaggedServiceIds(self::PREVIEW_GENERATOR_TAG);

        foreach (array_keys($previewGenerators) as $previewGeneratorId) {
            $registry->addMethodCall('register', [new Reference($previewGeneratorId)]);
        }
    }
}
