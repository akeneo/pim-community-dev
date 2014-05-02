<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Filtering by multi option backend type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsFilter extends EntityFilter
{
    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(AbstractAttribute $attribute, $operator, $value)
    {
        $joinAlias = 'filter'.$attribute->getCode().$this->aliasCounter++;

        // inner join to value
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias);
        $this->qb->innerJoin(
            $this->qb->getRootAlias().'.' . $attribute->getBackendStorage(),
            $joinAlias,
            'WITH',
            $condition
        );

        // prepare join on multi option value condition
        $backendType = $attribute->getBackendType();
        $joinAliasOpt = 'filterO'.$attribute->getCode().$this->aliasCounter;
        $backendField = sprintf('%s.%s', $joinAliasOpt, 'id');
        if (in_array('empty', $value)) {
            unset($value[array_search('empty', $value)]);
            $expr = $this->qb->expr()->isNull($backendField);

            if (count($value) > 0) {
                $exprIn = $this->qb->expr()->in($backendField, $value);
                $expr = $this->qb->expr()->orX($expr, $exprIn);
            }

            $this->qb->leftJoin($joinAlias.'.'.$backendType, $joinAliasOpt);
            $this->qb->andWhere($expr);
        } else {
            $expr = $this->qb->expr()->in($backendField, $value);
            $this->qb->innerJoin($joinAlias.'.'.$backendType, $joinAliasOpt, 'WITH', $expr);
        }

        return $this;
    }
}
