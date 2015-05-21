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
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ComparatorRegistry implements RegistryInterface
{
    const COMPARATOR_ATTRIBUTE = 'attribute';

    /** @var ComparatorInterface[] */
    protected $comparators = [];

    /**
     * {@inheritdoc}
     */
    public function getAttributeComparator($attributeType)
    {
        foreach ($this->getComparators(self::COMPARATOR_ATTRIBUTE) as $comparator) {
            if ($comparator->supportsComparison($attributeType)) {
                return $comparator;
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
     * {@inheritdoc}
     */
    public function addAttributeComparator(AttributeComparatorInterface $comparator, $priority)
    {
        $this->comparators[self::COMPARATOR_ATTRIBUTE][$priority][] = $comparator;
    }

    /**
     * Get the registered comparators
     *
     * @param string $type
     *
     * @return ComparatorInterface[]
     */
    protected function getComparators($type = self::COMPARATOR_ATTRIBUTE)
    {
        krsort($this->comparators[$type]);

        $comparators = [];
        foreach ($this->comparators[$type] as $groupedComparators) {
            $comparators = array_merge($comparators, $groupedComparators);
        }

        return $comparators;
    }
}
