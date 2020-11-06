<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Comparator;

/**
 * A registry of comparators
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ComparatorRegistryInterface
{
    /**
     * @param string $attributeType
     *
     * @throws \LogicException
     */
    public function getAttributeComparator(string $attributeType): \Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;

    /**
     * Add a comparator for attribute to the chain of comparators
     *
     * @param ComparatorInterface $comparator
     * @param int                 $priority
     */
    public function addAttributeComparator(ComparatorInterface $comparator, int $priority);

    /**
     * @param string $field
     *
     * @throws \LogicException
     */
    public function getFieldComparator(string $field): \Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;

    /**
     * Add a comparator for product's field to the chain of comparators
     *
     * @param ComparatorInterface $comparator
     * @param int                 $priority
     */
    public function addFieldComparator(ComparatorInterface $comparator, int $priority);
}
