<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Helper;

/**
 * Helper for sorting product values
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class SortProductValuesHelper
{
    /**
     * Sorts the provided values by attribute group and sort order
     *
     * @param \Pim\Bundle\CatalogBundle\Model\ProductValueInterface[] $values
     *
     * @return array
     */
    public function sort(array $values)
    {
        usort(
            $values,
            function ($first, $second) {
                $firstGroupOrder = $first->getAttribute()->getGroup()->getSortOrder();
                $secondGroupOrder = $second->getAttribute()->getGroup()->getSortOrder();

                if ($firstGroupOrder !== $secondGroupOrder) {
                    return $firstGroupOrder > $secondGroupOrder ? 1 : -1;
                }

                $firstAttrOrder = $first->getAttribute()->getSortOrder();
                $secondAttrOrder = $second->getAttribute()->getSortOrder();

                return $firstAttrOrder === $secondAttrOrder ? 0 : ($firstAttrOrder > $secondAttrOrder ? 1 : -1);
            }
        );

        $sortedValues = [];

        foreach ($values as $value) {
            $group = $value->getAttribute()->getGroup();

            $sortedValues[$group->getCode()]['groupLabel'] = $group->getLabel();
            $sortedValues[$group->getCode()]['values'][] = $value;
        }

        return $sortedValues;
    }
}
