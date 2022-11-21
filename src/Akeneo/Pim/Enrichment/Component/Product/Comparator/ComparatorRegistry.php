<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Comparator;

/**
 * A comparator that delegates comparison to a chain of comparators
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComparatorRegistry implements ComparatorRegistryInterface
{
    /** @staticvar string */
    const COMPARATOR_ATTRIBUTE = 'attribute';

    /** @staticvar string */
    const COMPARATOR_FIELD = 'field';

    /** @var ComparatorInterface[] */
    protected $comparators = [];

    /**
     * {@inheritdoc}
     */
    public function getAttributeComparator($attributeType)
    {
        foreach ($this->getComparators(self::COMPARATOR_ATTRIBUTE) as $comparator) {
            if ($comparator->supports($attributeType)) {
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
    public function addAttributeComparator(ComparatorInterface $comparator, $priority)
    {
        $this->comparators[self::COMPARATOR_ATTRIBUTE][$priority][] = $comparator;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldComparator($field)
    {
        foreach ($this->getComparators(self::COMPARATOR_FIELD) as $comparator) {
            if ($comparator->supports($field)) {
                return $comparator;
            }
        }

        throw new \LogicException(
            sprintf(
                'Cannot compare value of field "%s". ' .
                'Please check that a comparator exists for such field.',
                $field
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldComparator(ComparatorInterface $comparator, $priority)
    {
        $this->comparators[self::COMPARATOR_FIELD][$priority][] = $comparator;
    }

    /**
     * Get the registered comparators
     *
     * @param string $type
     *
     * @return ComparatorInterface[]
     */
    protected function getComparators($type)
    {
        $comparators = [];
        foreach ($this->comparators[$type] as $groupedComparators) {
            $comparators = array_merge($comparators, $groupedComparators);
        }

        return $comparators;
    }
}
