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
class RegisterPropositionPresentersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('pimee_workflow.twig.extension.proposition_changes')) {
            return;
        }

        $definition = $container->getDefinition('pimee_workflow.twig.extension.proposition_changes');
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
