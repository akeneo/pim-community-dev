<?php

namespace Oro\Bundle\WorkflowBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class AddConditionAndPostActionCompilerPass implements CompilerPassInterface
{
    const CONDITION_TAG = 'oro_workflow.condition';
    const CONDITION_FACTORY_SERVICE = 'oro_workflow.condition_factory';
    const POST_ACTION_TAG = 'oro_workflow.post_action';
    const POST_ACTION_FACTORY_SERVICE = 'oro_workflow.post_action_factory';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $this->injectEntityTypesByTag($container, self::CONDITION_FACTORY_SERVICE, self::CONDITION_TAG);
        $this->injectEntityTypesByTag($container, self::POST_ACTION_FACTORY_SERVICE, self::POST_ACTION_TAG);
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
