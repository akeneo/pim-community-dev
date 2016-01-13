<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\FilterBundle\Filter\ProductDraft;

use PimEnterprise\Bundle\CatalogBundle\Query\Filter\Operators;

/**
 * Extends ChoiceFilter in order to use a different operator that check an attribute code exists in the values
 * keys of a product draft changes, ensuring that a product contains at least one change on that attribute.
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 */
class AttributeChoiceFilter extends ChoiceFilter
{
    /**
     * {@inheritdoc}
     */
    protected function getOperator($type)
    {
        $operator = parent::getOperator($type);

        if (Operators::IN_LIST === $operator) {
            return Operators::IN_ARRAY_KEYS;
        }

        return $operator;
    }
}
