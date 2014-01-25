<?php

namespace Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\Query\Expr\Join;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Metric sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricSorter extends BaseSorter
{
    /**
     * {@inheritdoc}
     */
    public function add(AbstractAttribute $attribute, $direction)
    {
        $aliasPrefix = 'sorter';
        $joinAlias   = $aliasPrefix.'V'.$attribute->getCode().$this->aliasCounter++;
        $backendType = $attribute->getBackendType();

        // join to value
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias);
        $this->qb->leftJoin(
            $this->qb->getRootAlias().'.' . $attribute->getBackendStorage(),
            $joinAlias,
            'WITH',
            $condition
        );

        $joinAliasMetric = $aliasPrefix.'M'.$attribute->getCode().$this->aliasCounter;
        $this->qb->leftJoin($joinAlias.'.'.$backendType, $joinAliasMetric);

        $this->qb->addOrderBy($joinAliasMetric.'.baseData', $direction);

        return $this;
    }
}
