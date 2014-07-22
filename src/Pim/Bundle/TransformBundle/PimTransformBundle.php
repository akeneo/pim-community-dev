<?php

namespace Pim\Bundle\TransformBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\TransformBundle\DependencyInjection\Compiler;

/**
 * Transform bundle
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimTransformBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new Compiler\RegisterEntityTransformersPass())
            ->addCompilerPass(new Compiler\TransformerGuesserPass())
            ->addCompilerPass(
                new Compiler\ReplacePimSerializerArgumentsPass(
                    'pim_serializer',
                    ['pim_serializer.normalizer', 'pim_serializer.encoder']
                )
            );
    }
}
