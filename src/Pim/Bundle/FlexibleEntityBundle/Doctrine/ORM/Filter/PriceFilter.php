<?php

namespace Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\FlexibleEntityBundle\Exception\FlexibleQueryException;
use Pim\Bundle\FlexibleEntityBundle\Doctrine\FlexibleQueryBuilderInterface;
use Pim\Bundle\FlexibleEntityBundle\Doctrine\FilterInterface;

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
    public function add(AbstractAttribute $attribute, $operator, $value)
    {
        $backendType = $attribute->getBackendType();
        $joinAlias = 'filter'.$attribute->getCode().$this->aliasCounter++;

        // inner join to value
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias);
        $this->qb->innerJoin(
            $this->qb->getRootAlias().'.' . $attribute->getBackendStorage(),
            $joinAlias,
            'WITH',
            $condition
        );

        $joinAliasOpt = 'filterP'.$attribute->getCode().$this->aliasCounter;

        list($value, $currency) = explode(' ', $value);

        $currencyField = sprintf('%s.%s', $joinAliasOpt, 'currency');
        $currencyCondition = $this->prepareCriteriaCondition($currencyField, '=', $currency);

        $valueField = sprintf('%s.%s', $joinAliasOpt, 'data');
        $valueCondition = $this->prepareCriteriaCondition($valueField, $operator, $value);

        $condition = sprintf('(%s AND %s)', $currencyCondition, $valueCondition);
        $this->qb->innerJoin($joinAlias.'.'.$backendType, $joinAliasOpt, 'WITH', $condition);
    }
}
