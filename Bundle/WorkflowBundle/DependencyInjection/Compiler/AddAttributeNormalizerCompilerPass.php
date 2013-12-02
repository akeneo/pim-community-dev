<?php

namespace Oro\Bundle\WorkflowBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class AddAttributeNormalizerCompilerPass implements CompilerPassInterface
{
    const ATTRIBUTE_NORMALIZER_TAG = 'oro_workflow.attribute_normalizer';
    const NORMALIZER_SERVICE = 'oro_workflow.serializer.data.normalizer';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $normalizerDefinition = $container->getDefinition(self::NORMALIZER_SERVICE);
        foreach ($container->findTaggedServiceIds(self::ATTRIBUTE_NORMALIZER_TAG) as $id => $attributes) {
            $normalizerDefinition->addMethodCall('addAttributeNormalizer', array(new Reference($id)));
        }
    }
}
