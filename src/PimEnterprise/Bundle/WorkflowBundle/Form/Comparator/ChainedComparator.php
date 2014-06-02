<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

/**
 * A comparator that delegates comparison to a chain of comparators
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ChainedComparator implements ComparatorInterface
{
    /** @var ComparatorInterface[] */
    protected $comparators = [];

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
        foreach ($this->comparators as $comparator) {
            if ($comparator->supportsComparison($value)) {
                return $comparator->getChanges($value, $submittedData);
            }
        }

        throw new \LogicException(
            sprintf(
                'Cannot compare value of attribute type "%s". ' .
                'Please check that a comparator exists for such attribute type.',
                $value->getAttribute()->getAttributeType()
            )
        );
    }

    /**
     * Add a comparator to the chain of comparators
     *
     * @param ComparatorInterface $comparator
     */
    public function addComparator(ComparatorInterface $comparator)
    {
        $this->comparators[] = $comparator;
    }
}
