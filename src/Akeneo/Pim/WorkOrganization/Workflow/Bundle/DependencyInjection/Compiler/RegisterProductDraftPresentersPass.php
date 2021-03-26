<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass that register product draft value presenters into the product draft twig extension
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class RegisterProductDraftPresentersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('pimee_workflow.presenter.registry')) {
            return;
        }

        $definition = $container->getDefinition('pimee_workflow.presenter.registry');
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
