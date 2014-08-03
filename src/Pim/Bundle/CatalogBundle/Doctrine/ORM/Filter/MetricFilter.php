<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\Query\Expr\Join;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Metric filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricFilter extends BaseFilter
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

        if ($operator === 'EMPTY') {
            $this->qb->leftJoin(
                $this->qb->getRootAlias().'.values',
                $joinAlias,
                'WITH',
                $condition
            );

            $joinAliasOpt = 'filterM'.$attribute->getCode().$this->aliasCounter;
            $backendField = sprintf('%s.%s', $joinAliasOpt, 'baseData');
            $condition = $this->prepareCriteriaCondition($backendField, $operator, $value);
            $this->qb->leftJoin($joinAlias.'.'.$backendType, $joinAliasOpt);
            $this->qb->andWhere($condition);
        } else {
            $this->qb->innerJoin(
                $this->qb->getRootAlias().'.values',
                $joinAlias,
                'WITH',
                $condition
            );

            $joinAliasOpt = 'filterM'.$attribute->getCode().$this->aliasCounter;
            $backendField = sprintf('%s.%s', $joinAliasOpt, 'baseData');
            $condition = $this->prepareCriteriaCondition($backendField, $operator, $value);
            $this->qb->innerJoin($joinAlias.'.'.$backendType, $joinAliasOpt, 'WITH', $condition);
        }

        return $this;
    }
}
