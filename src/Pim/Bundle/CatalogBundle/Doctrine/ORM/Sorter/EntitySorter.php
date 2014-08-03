<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\Query\Expr\Join;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Entity sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntitySorter extends BaseSorter
{
    /**
     * {@inheritdoc}
     */
    public function addAttributeSorter(AbstractAttribute $attribute, $direction)
    {
        $aliasPrefix = 'sorter';
        $joinAlias   = $aliasPrefix.'V'.$attribute->getCode().$this->aliasCounter++;
        $backendType = $attribute->getBackendType();

        // join to value
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias);
        $this->qb->leftJoin(
            $this->qb->getRootAlias().'.values',
            $joinAlias,
            'WITH',
            $condition
        );

        // then to option and option value to sort on
        $joinAliasOpt = $aliasPrefix.'O'.$attribute->getCode().$this->aliasCounter;
        $condition    = $joinAliasOpt.".attribute = ".$attribute->getId();
        $this->qb->leftJoin($joinAlias.'.'.$backendType, $joinAliasOpt, 'WITH', $condition);

        $joinAliasOptVal = $aliasPrefix.'OV'.$attribute->getCode().$this->aliasCounter;
        $condition       = $joinAliasOptVal.'.locale = '.$this->qb->expr()->literal($this->context->getLocaleCode());
        $this->qb->leftJoin($joinAliasOpt.'.optionValues', $joinAliasOptVal, 'WITH', $condition);

        $this->qb->addOrderBy($joinAliasOpt.'.code', $direction);
        $this->qb->addOrderBy($joinAliasOptVal.'.value', $direction);

        return $this;
    }
}
