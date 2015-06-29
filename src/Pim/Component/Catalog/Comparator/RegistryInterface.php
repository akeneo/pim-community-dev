<?php

namespace Pim\Component\Catalog\Comparator;

/**
 * A comparator that delegates comparison to a chain of comparators
 * TODO: if it's a chain comparator, it should be named accordingly (ie: it's not a registry)
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO: ComparatorRegistryInterface as your object is called ComparatorRegistry
 */
interface RegistryInterface
{
    /**
     * @param string $attributeType
     *
     * @throws \LogicException
     *
     * @return ComparatorInterface
     */
    public function getAttributeComparator($attributeType);

    /**
     * Add a comparator for attribute to the chain of comparators
     *
     * @param ComparatorInterface $comparator
     * @param int                 $priority
     */
    public function addAttributeComparator(ComparatorInterface $comparator, $priority);

    /**
     * @param string $field
     *
     * @throws \LogicException
     *
     * @return ComparatorInterface
     */
    public function getFieldComparator($field);

    /**
     * Add a comparator for product's field to the chain of comparators
     *
     * @param ComparatorInterface $comparator
     * @param int                 $priority
     */
    public function addFieldComparator(ComparatorInterface $comparator, $priority);
}
