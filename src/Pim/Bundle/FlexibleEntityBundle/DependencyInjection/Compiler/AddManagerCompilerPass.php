<?php

namespace Pim\Bundle\FlexibleEntityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * CompilerPass to add flexible manager to connector
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddManagerCompilerPass implements CompilerPassInterface
{

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('pim_flexibleentity.registry')) {
            return;
        }

        $registryDefinition = $container->getDefinition('pim_flexibleentity.registry');
        $taggedServices = $container->findTaggedServiceIds('pim_flexibleentity_manager');

        foreach ($taggedServices as $managerId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $registryDefinition->addMethodCall(
                    'addManager',
                    array($managerId, new Reference($managerId), $attributes['entity'])
                );
            }
        }
    }
}
