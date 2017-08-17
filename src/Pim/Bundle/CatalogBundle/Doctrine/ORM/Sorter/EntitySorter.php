<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Join\ValueJoin;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Exception\ProductQueryException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Sorter\AttributeSorterInterface;

/**
 * Entity sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntitySorter implements AttributeSorterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /**
     * {@inheritdoc}
     */
    public function setQueryBuilder($queryBuilder)
    {
        $this->qb = $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array(
            $attribute->getType(),
            [
                AttributeTypes::OPTION_SIMPLE_SELECT
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeSorter(AttributeInterface $attribute, $direction, $locale = null, $scope = null)
    {
        $aliasPrefix = 'sorter';
        $joinAlias = $aliasPrefix.'V'.$attribute->getCode();
        $backendType = $attribute->getBackendType();

        if (null === $locale) {
            throw new \InvalidArgumentException(
                sprintf('Cannot prepare condition on type "%s" without locale', $attribute->getType())
            );
        }

        // join to value
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);
        $this->qb->leftJoin(
            $this->qb->getRootAlias().'.values',
            $joinAlias,
            'WITH',
            $condition
        );

        // then to option and option value to sort on
        $joinAliasOpt = $aliasPrefix.'O'.$attribute->getCode();
        $condition = $joinAliasOpt.".attribute = ".$attribute->getId();
        $this->qb->leftJoin($joinAlias.'.'.$backendType, $joinAliasOpt, 'WITH', $condition);

        $joinAliasOptVal = $aliasPrefix.'OV'.$attribute->getCode();
        $condition = $joinAliasOptVal.'.locale = '.$this->qb->expr()->literal($locale);
        $this->qb->leftJoin($joinAliasOpt.'.optionValues', $joinAliasOptVal, 'WITH', $condition);

        $this->qb->addSelect($joinAliasOptVal.'.value AS HIDDEN');
        $this->qb->addOrderBy($joinAliasOptVal.'.value', $direction);
        $this->qb->addOrderBy($joinAliasOpt.'.code', $direction);

        $idField = $this->qb->getRootAlias().'.id';
        $this->qb->addOrderBy($idField);

        return $this;
    }

    /**
     * Prepare join to attribute condition with current locale and scope criterias
     *
     * @param AttributeInterface $attribute the attribute
     * @param string             $joinAlias the value join alias
     * @param string             $locale    the locale
     * @param string             $scope     the scope
     *
     * @throws ProductQueryException
     *
     * @return string
     */
    protected function prepareAttributeJoinCondition(
        AttributeInterface $attribute,
        $joinAlias,
        $locale = null,
        $scope = null
    ) {
        $joinHelper = new ValueJoin($this->qb);

        return $joinHelper->prepareCondition($attribute, $joinAlias, $locale, $scope);
    }
}
