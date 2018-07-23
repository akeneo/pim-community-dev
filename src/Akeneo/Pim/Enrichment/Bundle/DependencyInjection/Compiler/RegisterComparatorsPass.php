<?php

namespace Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler;

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

        $attributeComparators = $this->sortComparatorsByPriority(
            $container->findTaggedServiceIds('pim_catalog.attribute.comparator')
        );
        foreach ($attributeComparators as $id => $attribute) {
            $container->getDefinition($id)->setPublic(false);
            $definition->addMethodCall(
                'addAttributeComparator',
                [
                    new Reference($id),
                    isset($attribute[0]['priority']) ? $attribute[0]['priority'] : 0
                ]
            );
        }

        $fieldComparators = $this->sortComparatorsByPriority(
            $container->findTaggedServiceIds('pim_catalog.field.comparator')
        );
        foreach ($fieldComparators as $id => $attribute) {
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

    /**
     * Sorts comparator services descending by their priority
     *
     * @param array $comparatorServices
     *
     * @return array
     */
    private function sortComparatorsByPriority(array $comparatorServices)
    {
        uasort($comparatorServices, function ($a, $b) {
            $priorityA = isset($a[0]['priority']) ? $a[0]['priority'] : 0;
            $priorityB = isset($b[0]['priority']) ? $b[0]['priority'] : 0;

            if ($priorityA > $priorityB) {
                return -1;
            }

            if ($priorityA < $priorityB) {
                return 1;
            }

            return 0;
        });

        return $comparatorServices;
    }
}
