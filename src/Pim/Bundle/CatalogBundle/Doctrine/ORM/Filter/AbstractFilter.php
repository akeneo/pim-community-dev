<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Akeneo\Component\StorageUtils\Exception\PropertyException;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Condition\CriteriaCondition;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Join\ValueJoin;
use Pim\Component\Catalog\Exception\ProductQueryException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\FilterInterface;

/**
 * Abstract ORM filter
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractFilter implements FilterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var array */
    protected $supportedOperators;

    /**
     * {@inheritdoc}
     */
    public function setQueryBuilder($queryBuilder)
    {
        if (!($queryBuilder instanceof QueryBuilder)) {
            throw new \InvalidArgumentException('Query builder should be an instance of Doctrine\ORM\QueryBuilder');
        }

        $this->qb = $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsOperator($operator)
    {
        return in_array($operator, $this->supportedOperators);
    }

    /**
     * {@inheritdoc}
     */
    public function getOperators()
    {
        return $this->supportedOperators;
    }

    /**
     * Get a unique alias
     *
     * @param string $alias
     *
     * @return string
     */
    protected function getUniqueAlias($alias)
    {
        return uniqid($alias);
    }

    /**
     * Prepare criteria condition with field, operator and value
     *
     * @param string|array $field    the backend field name
     * @param string|array $operator the operator used to filter
     * @param string|array $value    the value(s) to filter
     *
     * @throws PropertyException
     *
     * @return string
     */
    protected function prepareCriteriaCondition($field, $operator, $value)
    {
        $criteriaCondition = new CriteriaCondition($this->qb);

        return $criteriaCondition->prepareCriteriaCondition($field, $operator, $value);
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
