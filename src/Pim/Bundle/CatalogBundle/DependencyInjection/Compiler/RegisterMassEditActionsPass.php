<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register batch operations into the batch operator
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterMassEditActionsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('pim_catalog.mass_edit_action.operator')) {
            return;
        }

        $def = $container->getDefinition('pim_catalog.mass_edit_action.operator');

        foreach ($container->findTaggedServiceIds('pim_catalog.mass_edit_action') as $id => $config) {
            $def->addMethodCall(
                'registerMassEditAction',
                [
                    $config[0]['alias'],
                    new Reference($id),
                    isset($config[0]['acl']) ? $config[0]['acl'] : null
                ]
            );
        }
    }
}
