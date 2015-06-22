<?php

namespace Pim\Component\Catalog\Comparator;

/**
 * A comparator that delegates comparison to a chain of comparators
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * Add a comparator to the chain of comparators
     *
     * @param ComparatorInterface $comparator
     * @param int                 $priority
     */
    public function addAttributeComparator(ComparatorInterface $comparator, $priority);
}
