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
        $this->qb->innerJoin(
            $this->qb->getRootAlias().'.' . $attribute->getBackendStorage(),
            $joinAlias,
            'WITH',
            $condition
        );

        $joinAliasOpt = 'filterO'.$attribute->getCode().$this->aliasCounter;
        $backendField = sprintf('%s.%s', $joinAliasOpt, 'id');

        if (in_array('empty', $value)) {
            $expr = $this->qb->expr()->isNull($joinAlias .'.option');
            $this->qb->andWhere($expr);
        } else {
            $condition = $this->prepareCriteriaCondition($backendField, $operator, $value);

            $this->qb->innerJoin($joinAlias.'.'.$backendType, $joinAliasOpt, 'WITH', $condition);
        }

        return $this;
    }
}
