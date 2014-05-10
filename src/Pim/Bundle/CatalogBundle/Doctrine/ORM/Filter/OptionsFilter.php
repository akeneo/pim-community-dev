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
        $joinAlias    = 'filter'.$attribute->getCode().$this->aliasCounter++;
        $joinAliasOpt = 'filterO'.$attribute->getCode().$this->aliasCounter;
        $backendField = sprintf('%s.%s', $joinAliasOpt, 'id');

        if (in_array('empty', $value)) {
            $this->qb->leftJoin(
                $this->qb->getRootAlias().'.' . $attribute->getBackendStorage(),
                $joinAlias,
                'WITH',
                $this->prepareAttributeJoinCondition($attribute, $joinAlias)
            );

            $condition = $this->prepareEmptyCondition($backendField, $operator, $value);
            $this->qb
                ->leftJoin($joinAlias .'.'. $attribute->getBackendType(), $joinAliasOpt)
                ->andWhere($condition);
        } else {
            $this->qb
                ->innerJoin(
                    $this->qb->getRootAlias().'.' . $attribute->getBackendStorage(),
                    $joinAlias,
                    'WITH',
                    $this->prepareAttributeJoinCondition($attribute, $joinAlias)
                )
                ->innerJoin(
                    $joinAlias .'.'. $attribute->getBackendType(),
                    $joinAliasOpt,
                    'WITH',
                    $this->qb->expr()->in($backendField, $value)
                );
        }

        return $this;
    }

    /**
     * Prepare empty condition for options
     *
     * @param string $backendField
     * @param string $operator
     * @param string $value
     *
     * @return \Doctrine\ORM\Query\Expr
     */
    protected function prepareEmptyCondition($backendField, $operator, $value)
    {
        unset($value[array_search('empty', $value)]);
        $expr = $this->qb->expr()->isNull($backendField);

        if (count($value) > 0) {
            $exprIn = $this->qb->expr()->in($backendField, $value);
            $expr   = $this->qb->expr()->orX($expr, $exprIn);
        }

        return $expr;
    }
}
