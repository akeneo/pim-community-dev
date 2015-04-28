<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Comparator;

/**
 * Compare and get changes between a supported value and its relative updated data
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
interface ComparatorInterface
{
    /**
     * Whether or not the class supports comparison
     *
     * @param string $attributeType
     *
     * @return boolean
     */
    public function supportsComparison($attributeType);

    /**
     * Get the changes between a product value instance and the updated data
     * If no changes detected, then the method returns null
     *
     * @param array $changes
     * @param array $originals
     *
     * @return array|null
     */
    public function getChanges(array $changes, array $originals);
}
