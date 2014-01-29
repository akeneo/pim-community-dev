<?php

namespace Pim\Bundle\TransformBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Adds entity transformers to the entity transformer registry
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterEntityTransformersPass implements CompilerPassInterface
{
    /**
     * @staticvar string The registry service id
     */
    const TRANSFORMER_REGISTRY_SERVICE = 'pim_transform.transformer.registry';

    /**
     * @staticvar string The tag for entity transformers
     */
    const TRANSFORMER_TAG = 'pim_transform.transformer.entity';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(self::TRANSFORMER_REGISTRY_SERVICE);
        foreach ($container->findTaggedServiceIds(self::TRANSFORMER_TAG) as $serviceId => $tags) {
            if (!isset($tags[0]['entity'])) {
                throw new \LogicException(sprintf('The %s tag requires an entity property', self::TRANSFORMER_TAG));
            }
            $definition->addMethodCall('addEntityTransformer', array($tags[0]['entity'], $serviceId));
        }
    }
}
