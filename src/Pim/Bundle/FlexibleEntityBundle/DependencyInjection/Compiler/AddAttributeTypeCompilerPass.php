<?php

namespace Pim\Bundle\FlexibleEntityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * CompilerPass to add attribute type to factory
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddAttributeTypeCompilerPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    const FLEXIBLE_TYPE_TAG         = 'pim_flexibleentity.attributetype';

    /**
     * @var string
     */
    const FLEXIBLE_TYPE_FACTORY_KEY = 'pim_flexibleentity.attributetype.factory';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $factory = $container->getDefinition(self::FLEXIBLE_TYPE_FACTORY_KEY);

        $types   = array();
        foreach ($container->findTaggedServiceIds(self::FLEXIBLE_TYPE_TAG) as $id => $attributes) {
            $attributes = current($attributes);
            $alias = $attributes['alias'];
            $entity = $attributes['entity'];
            $attributeType = $container->getDefinition($id);
            $types[$alias]= array('type' => $attributeType, 'entity' => $entity);
        }

        $factory->replaceArgument(0, $types);
    }
}
