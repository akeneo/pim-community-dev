<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Doctrine\Operators;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Condition\CriteriaCondition;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Join\ValueJoin;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Exception\ProductQueryException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Price filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceFilter implements AttributeFilterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var array */
    protected $supportedAttributes;

    /** @var array */
    protected $supportedOperators;

    /**
     * Instanciate the base filter
     *
     * @param array $supportedAttributes
     * @param array $supportedOperators
     */
    public function __construct(
        array $supportedAttributes = [],
        array $supportedOperators = []
    ) {
        $this->supportedAttributes = $supportedAttributes;
        $this->supportedOperators = $supportedOperators;
    }

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
    public function addAttributeFilter(AttributeInterface $attribute, $operator, $value, array $context = [])
    {
        $backendType = $attribute->getBackendType();
        $joinAlias = 'filter'.$attribute->getCode();

        // join to value
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $context);

        if (Operators::IS_EMPTY === $operator) {
            $this->qb->leftJoin(
                $this->qb->getRootAlias().'.values',
                $joinAlias,
                'WITH',
                $condition
            );

            // join to price
            $joinAliasPrice = 'filterP'.$attribute->getCode();
            $priceData      = $joinAlias.'.'.$backendType;
            $this->qb->leftJoin($priceData, $joinAliasPrice);

            // add conditions
            $condition = $this->preparePriceCondition($joinAliasPrice, $operator, $value);
            $exprNull = $this->qb->expr()->isNull($joinAliasPrice.'.id');
            $exprOr = $this->qb->expr()->orX($condition, $exprNull);
            $this->qb->andWhere($exprOr);
        } else {
            $this->qb->innerJoin(
                $this->qb->getRootAlias().'.values',
                $joinAlias,
                'WITH',
                $condition
            );

            $joinAliasPrice = 'filterP'.$attribute->getCode();
            $condition = $this->preparePriceCondition($joinAliasPrice, $operator, $value);

            $this->qb->innerJoin($joinAlias.'.'.$backendType, $joinAliasPrice, 'WITH', $condition);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array(
            $attribute->getAttributeType(),
            $this->supportedAttributes
        );
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
     * Prepare criteria condition with field, operator and value
     *
     * @param string|array $field    the backend field name
     * @param string|array $operator the operator used to filter
     * @param string|array $value    the value(s) to filter
     *
     * @return string
     * @throws ProductQueryException
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
     * @param array              $context   the context
     *
     * @throws ProductQueryException
     *
     * @return string
     */
    protected function prepareAttributeJoinCondition(AttributeInterface $attribute, $joinAlias, array $context)
    {
        $joinHelper = new ValueJoin($this->qb);

        return $joinHelper->prepareCondition($attribute, $joinAlias, $context);
    }

    /**
     * Prepare price condition to join
     *
     * @param string $joinAlias
     * @param string $operator
     * @param string $value
     *
     * @return string
     */
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
