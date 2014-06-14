<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

/**
 * Compare and get changes between a supported value and its relative submitted data
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface ComparatorInterface
{
    /**
     * Wether or not the class suppports comparison of a product value instance
     *
     * @param AbstractProductValue $value
     *
     * @return boolean
     */
    public function supportsComparison(AbstractProductValue $value);

    /**
     * Get the changes between a product value instance and the submitted data
     * If no changes detected, then the method returns null
     *
     * N.B.: Submitted data are casted into string, be carefull not to use type-checking operators (===, !==, ...)
     * when comparing them to value data. Instead use simple equality operators (==, !=, ...)
     *
     * @param AbstractProductValue $value
     * @param array                $submittedData
     *
     * @return array|null
     */
    public function getChanges(AbstractProductValue $value, $submittedData);
}
