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

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Compare and get changes between a supported value and its relative submitted data
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
interface ComparatorInterface
{
    /**
     * Wether or not the class suppports comparison of a product value instance
     *
     * @param ProductValueInterface $value
     *
     * @return boolean
     */
    public function supportsComparison(ProductValueInterface $value);

    /**
     * Get the changes between a product value instance and the submitted data
     * If no changes detected, then the method returns null
     *
     * N.B.: Submitted data are casted into string, be carefull not to use type-checking operators (===, !==, ...)
     * when comparing them to value data. Instead use simple equality operators (==, !=, ...)
     *
     * @param ProductValueInterface $value
     * @param array                 $submittedData
     *
     * @return array|null
     */
    public function getChanges(ProductValueInterface $value, $submittedData);
}
