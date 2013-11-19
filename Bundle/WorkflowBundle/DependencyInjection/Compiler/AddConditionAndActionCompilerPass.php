<?php

namespace Oro\Bundle\WorkflowBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class AddConditionAndActionCompilerPass implements CompilerPassInterface
{
    const CONDITION_TAG = 'oro_workflow.condition';
    const CONDITION_FACTORY_SERVICE = 'oro_workflow.condition_factory';
    const ACTION_TAG = 'oro_workflow.action';
    const ACTION_FACTORY_SERVICE = 'oro_workflow.action_factory';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $this->injectEntityTypesByTag($container, self::CONDITION_FACTORY_SERVICE, self::CONDITION_TAG);
        $this->injectEntityTypesByTag($container, self::ACTION_FACTORY_SERVICE, self::ACTION_TAG);
    }

    /**
     * @param ContainerBuilder $container
     * @param string $serviceId
     * @param string $tagName
     */
    protected function injectEntityTypesByTag(ContainerBuilder $container, $serviceId, $tagName)
    {
        $types = array();

        foreach ($container->findTaggedServiceIds($tagName) as $id => $attributes) {
            $container->getDefinition($id)->setScope(ContainerInterface::SCOPE_PROTOTYPE);

            foreach ($attributes as $eachTag) {
                if (!empty($eachTag['alias'])) {
                    $aliases = explode('|', $eachTag['alias']);
                } else {
                    $aliases = array($id);
                }
                foreach ($aliases as $alias) {
                    $types[$alias] = $id;
                }
            }
        }

        $definition = $container->getDefinition($serviceId);
        $definition->replaceArgument(1, $types);
    }
}
