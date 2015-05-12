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

/**
 * A comparator that delegates comparison to a chain of comparators
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 *
 * @see    PimEnterprise\Bundle\WorkflowBundle\Form\ComparatorInterface
 */
class ChainedComparator
{
    /** @var ComparatorInterface[] */
    protected $comparators = [];

    /**
     * @param string $attributeType
     * @param array  $changes
     * @param array  $originals
     *
     * @throws \LogicException
     *
     * @return array|null
     */
    public function compare($attributeType, array $changes, array $originals)
    {
        foreach ($this->getComparators() as $comparator) {
            if ($comparator->supportsComparison($attributeType)) {
                return $comparator->getChanges($changes, $originals);
            }
        }

        throw new \LogicException(
            sprintf(
                'Cannot compare value of attribute type "%s". ' .
                'Please check that a comparator exists for such attribute type.',
                $attributeType
            )
        );
    }

    /**
     * Add a comparator to the chain of comparators
     *
     * @param ComparatorInterface $comparator
     * @param int                 $priority
     */
    public function addComparator(ComparatorInterface $comparator, $priority)
    {
        $this->comparators[$priority][] = $comparator;
    }

    /**
     * Get the registered comparators
     *
     * @return ComparatorInterface[]
     */
    public function getComparators()
    {
        krsort($this->comparators);

        $comparators = [];
        foreach ($this->comparators as $groupedComparators) {
            $comparators = array_merge($comparators, $groupedComparators);
        }

        return $comparators;
    }
}
