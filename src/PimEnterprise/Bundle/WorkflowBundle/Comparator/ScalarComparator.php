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
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Comparator which calculate change set for scalars
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 *
 * @see    PimEnterprise\Bundle\WorkflowBundle\Form\ComparatorInterface
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
    public function supportsComparison(ProductValueInterface $value)
    {
        $data = $value->getData();

        return is_null($data) || is_scalar($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(ProductValueInterface $value, $submittedData)
    {
        foreach ($submittedData as $key => $submittedValue) {
            if ($key === 'id') {
                continue;
            }
            if ($this->accessor->getValue($value, $key) != $submittedValue) {
                return [
                    $key => $submittedValue
                ];
            }
        }
    }
}
