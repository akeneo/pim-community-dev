<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass that register product data publishers into the chained publisher
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
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
