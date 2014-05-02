<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Filtering by option backend type
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
        $backendType = $attribute->getBackendType();
        $joinAlias = 'filter'.$attribute->getCode().$this->aliasCounter++;

        // inner join to value
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias);

        $optionAlias = $joinAlias .'.option';
        if (in_array('empty', $value)) {
            unset($value[array_search('empty', $value)]);
            $expr = $this->qb->expr()->isNull($optionAlias);

            if (count($value) > 0) {
                $exprIn = $this->qb->expr()->in($optionAlias, $value);
                $expr = $this->qb->expr()->orX($expr, $exprIn);
            }
        } else {
            $expr = $this->qb->expr()->in($optionAlias, $value);
        }

        $condition .= ' AND ( '. $expr .' ) ';

        $this->qb->innerJoin(
            $this->qb->getRootAlias().'.' . $attribute->getBackendStorage(),
            $joinAlias,
            'WITH',
            $condition
        );


        // SELECT     p0_.*
        // FROM       pim_catalog_product p0_
        // INNER JOIN pim_catalog_product_value p1_ ON p0_.id = p1_.entity_id
        //        AND (p1_.attribute_id = 4 AND (p1_.option_id IN ('11') OR p1_.option_id IS NULL))
        // GROUP BY   p0_.id;

//         $joinAliasOpt = 'filterO'.$attribute->getCode().$this->aliasCounter;
//         $backendField = sprintf('%s.%s', $joinAliasOpt, 'id');

//         if (in_array('empty', $value)) {
//             $expr = $this->qb->expr()->isNull($joinAlias .'.option');
//             $this->qb->andWhere($expr);
//         } else {
//             $condition = $this->prepareCriteriaCondition($backendField, $operator, $value);

//             $this->qb->innerJoin($joinAlias.'.'.$backendType, $joinAliasOpt, 'WITH', $condition);
//         }

        return $this;
    }
}
