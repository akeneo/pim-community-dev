<?php

namespace Akeneo\Pim\Structure\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register attribute type to registry
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterAttributeTypePass implements CompilerPassInterface
{
    /** @staticvar string */
    const ATTRIBUTE_TYPE_TAG = 'pim_catalog.attribute_type';

    /** @staticvar string */
    const ATTRIBUTE_TYPE_REGISTRY = 'pim_catalog.registry.attribute_type';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::ATTRIBUTE_TYPE_REGISTRY)) {
            throw new \LogicException('Attribute type registry must be configured');
        }

        $registry = $container->getDefinition(self::ATTRIBUTE_TYPE_REGISTRY);
        $taggedServices = $container->findTaggedServiceIds(self::ATTRIBUTE_TYPE_TAG);

        foreach ($taggedServices as $id => $attributes) {
            $attributes = current($attributes);
            $alias = $attributes['alias'];

            $registry->addMethodCall('register', [$alias, new Reference($id)]);
        }
    }
}
