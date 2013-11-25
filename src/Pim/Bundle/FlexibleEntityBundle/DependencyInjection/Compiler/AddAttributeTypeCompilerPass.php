<?php

namespace Pim\Bundle\FlexibleEntityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * CompilerPass to add attribute type to factory
 */
class AddAttributeTypeCompilerPass implements CompilerPassInterface
{
    const FLEXIBLE_TYPE_TAG         = 'pim_flexibleentity.attributetype';
    const FLEXIBLE_TYPE_FACTORY_KEY = 'pim_flexibleentity.attributetype.factory';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $this->injectEntityTypesByTag($container, self::FLEXIBLE_TYPE_FACTORY_KEY, self::FLEXIBLE_TYPE_TAG);
    }

    /**
     * @param ContainerBuilder $container the container
     * @param string           $serviceId the service
     * @param string           $tagName   the tag
     */
    protected function injectEntityTypesByTag(ContainerBuilder $container, $serviceId, $tagName)
    {
        $definition = $container->getDefinition($serviceId);
        $types      = array();

        foreach ($container->findTaggedServiceIds($tagName) as $id => $attributes) {
            $container->getDefinition($id);

            foreach ($attributes as $eachTag) {
                $index = !empty($eachTag['alias']) ? $eachTag['alias'] : $id;
                $types[$index] = $id;
            }
        }

        $definition->replaceArgument(1, $types);
    }
}
