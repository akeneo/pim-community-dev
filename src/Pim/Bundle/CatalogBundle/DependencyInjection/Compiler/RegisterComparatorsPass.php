<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass that register product draft value presenters into the product draft twig extension
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterComparatorsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('pim_catalog.comparator.registry')) {
            return;
        }

        $definition = $container->getDefinition('pim_catalog.comparator.registry');
        foreach ($container->findTaggedServiceIds('pim_catalog.attribute.comparator') as $id => $attribute) {
            $container->getDefinition($id)->setPublic(false);
            $definition->addMethodCall(
                'addAttributeComparator',
                [
                    new Reference($id),
                    isset($attribute[0]['priority']) ? $attribute[0]['priority'] : 0
                ]
            );
        }

        foreach ($container->findTaggedServiceIds('pim_catalog.field.comparator') as $id => $attribute) {
            $container->getDefinition($id)->setPublic(false);
            $definition->addMethodCall(
                'addFieldComparator',
                [
                    new Reference($id),
                    isset($attribute[0]['priority']) ? $attribute[0]['priority'] : 0
                ]
            );
        }
    }
}
