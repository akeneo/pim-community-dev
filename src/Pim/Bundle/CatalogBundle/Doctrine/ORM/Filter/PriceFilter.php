<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Exception\ProductQueryException;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\ValueJoin;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\CriteriaCondition;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

/**
 * Price filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceFilter implements AttributeFilterInterface
{
    /**
     * @var QueryBuilder
     */
    protected $qb;

    /** @var CatalogContext */
    protected $context;

    /**
     * TODO : replace by a timestamp ?
     * Alias counter, to avoid duplicate alias name
     * @return integer
     */
    protected $aliasCounter = 1;

    /**
     * Instanciate the base filter
     *
     * @param CatalogContext $context
     */
    public function __construct(CatalogContext $context)
    {
        $this->context = $context;
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
    public function addAttributeFilter(AbstractAttribute $attribute, $operator, $value)
    {
        $backendType = $attribute->getBackendType();
        $joinAlias = 'filter'.$attribute->getCode().$this->aliasCounter++;

        // join to value
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias);

        if ('EMPTY' === $operator) {
            $this->qb->leftJoin(
                $this->qb->getRootAlias().'.values',
                $joinAlias,
                'WITH',
                $condition
            );

            // join to price
            $joinAliasPrice = 'filterP'.$attribute->getCode().$this->aliasCounter;
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

            $joinAliasPrice = 'filterP'.$attribute->getCode().$this->aliasCounter;
            $condition = $this->preparePriceCondition($joinAliasPrice, $operator, $value);
            $this->qb->innerJoin($joinAlias.'.'.$backendType, $joinAliasPrice, 'WITH', $condition);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AbstractAttribute $attribute)
    {
        return $attribute->getAttributeType() === 'pim_catalog_price_collection';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsOperator($operator)
    {
        return in_array($operator, ['=', '<', '<=', '>', '>=', 'EMPTY']);
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
     * @param AbstractAttribute $attribute the attribute
     * @param string            $joinAlias the value join alias
     *
     * @throws ProductQueryException
     *
     * @return string
     */
    protected function prepareAttributeJoinCondition(AbstractAttribute $attribute, $joinAlias)
    {
        $joinHelper = new ValueJoin($this->qb, $this->context);

        return $joinHelper->prepareCondition($attribute, $joinAlias);
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
