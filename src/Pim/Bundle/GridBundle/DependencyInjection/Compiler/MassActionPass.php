<?php

namespace Pim\Bundle\GridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds access to ACLs to MassActionDispatcher
 * 
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassActionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->get('oro_grid.mass_action.dispatcher')) {
            return;
        }
        $container->getDefinition('oro_grid.mass_action.dispatcher')
            ->addMethodCall(
                'setACLManager',
                array(new Reference('oro_user.acl_manager'))
        );
    }
}
