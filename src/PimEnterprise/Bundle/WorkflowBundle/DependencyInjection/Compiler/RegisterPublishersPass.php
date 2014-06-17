<?php

namespace PimEnterprise\Bundle\WorkflowBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass that register product data publishers into the chained publisher
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class RegisterPublishersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('pimee_workflow.publisher.chained')) {
            return;
        }

        $definition = $container->getDefinition('pimee_workflow.publisher.chained');
        foreach (array_keys($container->findTaggedServiceIds('pimee_workflow.publisher')) as $id) {
            $container->getDefinition($id)->setPublic(false);
            $definition->addMethodCall('addPublisher', [new Reference($id)]);
        }
    }
}
