<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\Query\Expr\Join;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Price filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceFilter extends BaseFilter
{
    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(AbstractAttribute $attribute, $operator, $value)
    {
        $backendType = $attribute->getBackendType();
        $joinAlias = 'filter'.$attribute->getCode().$this->aliasCounter++;

        // join to value
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias);

        if ('EMPTY' === $operator) {
            $this->qb->leftJoin(
                $this->qb->getRootAlias().'.' . $attribute->getBackendStorage(),
                $joinAlias,
                'WITH',
                $condition
            );

            // join to price
            $joinAliasPrice = 'filterP'.$attribute->getCode().$this->aliasCounter;
            $priceData      = $joinAlias.'.'.$backendType;
            $this->qb->leftJoin($priceData, $joinAliasPrice);

            // add conditions
            $condition = $this->preparePriceCondition($joinAliasPrice, $operator, $value);
            $exprNull = $this->qb->expr()->isNull($joinAliasPrice.'.id');
            $exprOr = $this->qb->expr()->orX($condition, $exprNull);
            $this->qb->andWhere($exprOr);
        } else {
            $this->qb->innerJoin(
                $this->qb->getRootAlias().'.' . $attribute->getBackendStorage(),
                $joinAlias,
                'WITH',
                $condition
            );

            $joinAliasPrice = 'filterP'.$attribute->getCode().$this->aliasCounter;
            $condition = $this->preparePriceCondition($joinAliasPrice, $operator, $value);
            $this->qb->innerJoin($joinAlias.'.'.$backendType, $joinAliasPrice, 'WITH', $condition);
        }

        return $this;
    }

    protected function preparePriceCondition($joinAlias, $operator, $value)
    {
        list($value, $currency) = explode(' ', $value);

        $valueField     = sprintf('%s.%s', $joinAlias, 'data');
        $valueCondition = $this->prepareCriteriaCondition($valueField, $operator, $value);

        $currencyField     = sprintf('%s.%s', $joinAlias, 'currency');
        $currencyCondition = $this->prepareCriteriaCondition($currencyField, '=', $currency);

        return sprintf('%s AND %s', $currencyCondition, $valueCondition);
    }
}
