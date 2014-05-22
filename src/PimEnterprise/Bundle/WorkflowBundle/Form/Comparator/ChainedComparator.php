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
class ChainedComparator implements ComparatorInterface
{
    protected $comparators = [];

    public function supportsComparison(AbstractProductValue $value)
    {
        return true;
    }

    public function getChanges(AbstractProductValue $value, $submittedData)
    {
        foreach ($this->comparators as $comparator) {
            if ($comparator->supportsComparison($value)) {
                return $comparator->getChanges($value, $submittedData);
            }
        }
    }

    public function addComparator(ComparatorInterface $comparator)
    {
        $this->comparators[] = $comparator;
    }
}
