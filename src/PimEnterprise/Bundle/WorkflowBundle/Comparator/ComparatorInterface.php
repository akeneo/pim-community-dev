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
     * @return bool
     */
    public function supportsComparison($attributeType);

    /**
     * Get the changes between a normalized product value instance and the updated data
     * If no changes detected, then the method returns null
     *
     * @param array $data
     * @param array $originals
     *
     * @return array|null
     */
    public function getChanges(array $data, array $originals);
}
