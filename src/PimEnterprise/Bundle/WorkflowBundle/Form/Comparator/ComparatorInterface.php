<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

/**
 * PimEnterprise\Bundle\WorkflowBundle\Form\Comparator
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface ComparatorInterface
{
    public function supportsComparison(AbstractProductValue $value);

    public function getChanges(AbstractProductValue $value, $submittedData);
}
