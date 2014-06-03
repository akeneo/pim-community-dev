<?php

namespace PimEnterprise\Bundle\WorkflowBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass that register proposal value presenters into the proposal twig extension
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class RegisterProposalPresentersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('pimee_workflow.twig.extension.proposal_changes')) {
            return;
        }

        $definition = $container->getDefinition('pimee_workflow.twig.extension.proposal_changes');
        foreach ($container->findTaggedServiceIds('pimee_workflow.presenter') as $id => $attribute) {

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
