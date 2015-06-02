<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\FileTransformerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register all Transformation in registry
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class RegisterTransformationPass implements CompilerPassInterface
{
    /** @staticvar */
    const TRANSFORMATION_REGISTRY = 'akeneo_file_transformer.transformation.registry';

    /** @staticvar */
    const TRANSFORMATION_TAG = 'akeneo_file_transformer.transformation';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(self::TRANSFORMATION_REGISTRY);
        $transformations = $container->findTaggedServiceIds(self::TRANSFORMATION_TAG);

        foreach (array_keys($transformations) as $transformationId) {
            $registry->addMethodCall('add', [new Reference($transformationId)]);
        }
    }
}
