<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

/**
 * PimEnterprise\Bundle\WorkflowBundle\Form\Comparator
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ComparatorInterface
{
    public function supportsComparison(AbstractProductValue $value);

    public function getChanges(AbstractProductValue $value, $submittedData);
}
