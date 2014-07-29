<?php

namespace PimEnterprise\Bundle\WorkflowBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass that register proposition value presenters into the proposition twig extension
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class RegisterComparatorsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('pimee_workflow.comparator.chained')) {
            return;
        }

        $definition = $container->getDefinition('pimee_workflow.comparator.chained');
        foreach ($container->findTaggedServiceIds('pimee_workflow.comparator') as $id => $attribute) {

            $container->getDefinition($id)->setPublic(false);
            $definition->addMethodCall(
                'addComparator',
                [
                    new Reference($id),
                    isset($attribute[0]['priority']) ? $attribute[0]['priority'] : 0
                ]
            );

        }
    }
}
