<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\Query\Expr\Join;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Entity filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityFilter extends BaseFilter
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
        $this->qb->innerJoin(
            $this->qb->getRootAlias().'.values',
            $joinAlias,
            'WITH',
            $condition
        );

        $joinAliasOpt = 'filterO'.$attribute->getCode().$this->aliasCounter;
        $backendField = sprintf('%s.%s', $joinAliasOpt, 'id');
        $condition = $this->prepareCriteriaCondition($backendField, $operator, $value);

        $this->qb->innerJoin($joinAlias.'.'.$backendType, $joinAliasOpt, 'WITH', $condition);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value)
    {
        $rootAlias  = $this->qb->getRootAlias();
        $entityAlias = 'filter'.$field;
        $this->qb->leftJoin($rootAlias.'.'.$field, $entityAlias);

        if ($operator === 'NOT IN') {
            $this->qb->andWhere(
                $this->qb->expr()->orX(
                    $this->qb->expr()->notIn($entityAlias.'.id', $value),
                    $this->qb->expr()->isNull($entityAlias.'.id')
                )
            );
        } else {
            if (in_array('empty', $value)) {
                unset($value[array_search('empty', $value)]);
                $exprNull = $this->qb->expr()->isNull($entityAlias.'.id');

                if (count($value) > 0) {
                    $exprIn = $this->qb->expr()->in($entityAlias.'.id', $value);
                    $expr = $this->qb->expr()->orX($exprNull, $exprIn);
                } else {
                    $expr = $exprNull;
                }
            } else {
                $expr = $this->qb->expr()->in($entityAlias.'.id', $value);
            }

            $this->qb->andWhere($expr);
        }

        return $this;
    }
}
