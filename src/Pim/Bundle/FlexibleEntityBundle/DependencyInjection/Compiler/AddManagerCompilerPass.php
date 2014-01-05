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
        $entitiesConfig = array();
        foreach ($taggedServices as $managerId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $entity = $attributes['entity'];
                $registryDefinition->addMethodCall(
                    'addManager',
                    array($managerId, $container->getDefinition($managerId), $entity)
                );
                $entitiesConfig['entities_config'][$entity]= $managerId;
            }
        }
        $container->setParameter('pim_flexibleentity.flexible_config', $entitiesConfig);
    }
}
