<?php

namespace Pim\Component\Catalog\Comparator;

/**
 * A comparator that delegates comparison to a chain of comparators
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComparatorRegistry implements RegistryInterface
{
    /** @staticvar string */
    const COMPARATOR_ATTRIBUTE = 'attribute';

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
