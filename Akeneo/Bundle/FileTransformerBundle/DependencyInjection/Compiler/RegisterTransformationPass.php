<?php

/*
* This file is part of the Akeneo PIM Enterprise Edition.
*
* (c) 2015 Akeneo SAS (http://www.akeneo.com)
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Akeneo\Component\FileTransformer\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register all Transformation in registry
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class RegisterTransformationPass extends CompilerPassInterface
{
    /** @staticvar */
    const TransformationRegistry = 'akeneo_file_transformer.transformation.registry';

    /** @staticvar */
    const TransformationTag = 'akeneo_file_transformer.transformation';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(self::TransformationRegistry);
        $transformations = $container->findTaggedServiceIds(self::TransformationTag);

        foreach (array_keys($transformations) as $transformationId) {
            $registry->addMethodCall('add', [new Reference($transformationId)]);
        }
    }
}
