<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Comparator;

/**
 * A comparator that delegates comparison to a chain of comparators
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
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
