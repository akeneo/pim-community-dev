<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ScalarComparator implements ComparatorInterface
{
    protected $accessor;

    public function __construct(PropertyAccessor $accessor = null)
    {
        $this->accessor = $accessor ?: PropertyAccess::createPropertyAccessor();
    }

    public function supportsComparison(AbstractProductValue $value)
    {
        return true;
    }

    public function getChanges(AbstractProductValue $value, $submittedData)
    {
        foreach ($submittedData as $key => $submittedValue) {
            if ($key === 'id') {
                continue;
            }
            if ($this->accessor->getValue($value, $key) != $submittedValue) {
                return [$key => $submittedValue];
            }
        }
    }
}
