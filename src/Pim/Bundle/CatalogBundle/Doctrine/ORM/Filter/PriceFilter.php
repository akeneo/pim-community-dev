<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Doctrine\Query\Operators;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Price filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceFilter extends AbstractFilter implements AttributeFilterInterface
{
    /** @var array */
    protected $supportedAttributes;

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
        $this->supportedOperators  = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(AttributeInterface $attribute, $operator, $value, $locale = null, $scope = null)
    {
        if (!is_string($value)) {
            throw InvalidArgumentException::stringExpected($attribute->getCode(), 'filter', 'price');
        }

        $backendType = $attribute->getBackendType();
        $joinAlias = 'filter'.$attribute->getCode();

        // join to value
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);

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
        return in_array($attribute->getAttributeType(), $this->supportedAttributes);
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
