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
 * Compiler pass that registers product value presenters into the product value presenter twig extension
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class RegisterProductValuePresentersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('pimee_workflow.twig.extension.product_value_presenter')) {
            $definition = $container->getDefinition('pimee_workflow.twig.extension.product_value_presenter');
            foreach ($container->findTaggedServiceIds('pimee_workflow.product_value_presenter') as $id => $attribute) {
                $container->getDefinition($id)->setPublic(false);
                $definition->addMethodCall(
                    'addPresenter',
                    [
                        new Reference($id),
                        isset($attribute[0]['priority']) ? $attribute[0]['priority'] : 0
                    ]
                );
            }
        }
    }
}
