<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Filtering by simple option backend type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionFilter extends EntityFilter
{
    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(AbstractAttribute $attribute, $operator, $value)
    {
        $joinAlias = 'filter'.$attribute->getCode().$this->aliasCounter++;

        // prepare join value condition
        $optionAlias = $joinAlias .'.option';
        if (in_array('empty', $value)) {
            unset($value[array_search('empty', $value)]);
            $expr = $this->qb->expr()->isNull($optionAlias);

            if (count($value) > 0) {
                $exprIn = $this->qb->expr()->in($optionAlias, $value);
                $expr = $this->qb->expr()->orX($expr, $exprIn);
            }

            $this->qb->leftJoin(
                $this->qb->getRootAlias().'.values',
                $joinAlias,
                'WITH',
                $this->prepareAttributeJoinCondition($attribute, $joinAlias)
            );
            $this->qb->andWhere($expr);
        } else {
            // inner join to value
            $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias);
            $condition .= ' AND ( '. $this->qb->expr()->in($optionAlias, $value) .' ) ';

            $this->qb->innerJoin(
                $this->qb->getRootAlias().'.values',
                $joinAlias,
                'WITH',
                $condition
            );
        }

        return $this;
    }
}
