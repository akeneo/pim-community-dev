<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 *
 * @see PimEnterprise\Bundle\WorkflowBundle\Form\ComparatorInterface
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ScalarComparator implements ComparatorInterface
{
    /** @var PropertyAccessor */
    protected $accessor;

    /**
     * Construct
     *
     * @param PropertyAccessor $accessor
     */
    public function __construct(PropertyAccessor $accessor = null)
    {
        $this->accessor = $accessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsComparison(AbstractProductValue $value)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(AbstractProductValue $value, $submittedData)
    {
        foreach ($submittedData as $key => $submittedValue) {
            if ($key === 'id') {
                continue;
            }
            if ($this->accessor->getValue($value, $key) != $submittedValue) {
                return [
                    'id' => $submittedData['id'],
                    $key => $submittedValue
                ];
            }
        }
    }
}
