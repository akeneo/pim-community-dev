<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;

/**
 * Price filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var CurrencyManager */
    protected $currencyManager;

    /** @var array */
    protected $supportedAttributes;

    /**
     * Instanciate the base filter
     *
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param CurrencyManager          $currencyManager
     * @param array                    $supportedAttributes
     * @param array                    $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        CurrencyManager $currencyManager,
        array $supportedAttributes = [],
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
        $this->currencyManager     = $currencyManager;
        $this->supportedAttributes = $supportedAttributes;
        $this->supportedOperators  = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(
        AttributeInterface $attribute,
        $operator,
        $value,
        $locale = null,
        $scope = null,
        $options = []
    ) {
        $this->checkLocaleAndScope($attribute, $locale, $scope, 'price');
        $this->checkValue($attribute, $value);

        if (Operators::IS_EMPTY !== $operator) {
            $this->addNonEmptyFilter($attribute, $value, $operator, $locale, $scope);
        } else {
            $this->addEmptyFilter($attribute, $value, $locale, $scope);
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
     * @param AttributeInterface $attribute
     * @param array              $value
     * @param string             $locale
     * @param string             $scope
     */
    protected function addEmptyFilter(
        AttributeInterface $attribute,
        array $value,
        $locale = null,
        $scope = null
    ) {
        $backendType = $attribute->getBackendType();
        $joinAlias = $this->getUniqueAlias('filter' . $attribute->getCode(), true);

        // join to value
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);

        $this->qb->leftJoin(
            $this->qb->getRootAlias() . '.values',
            $joinAlias,
            'WITH',
            $condition
        );

        // join to price
        $joinAliasPrice = $this->getUniqueAlias('filterP' . $attribute->getCode());
        $priceData      = $joinAlias . '.' . $backendType;
        $this->qb->leftJoin($priceData, $joinAliasPrice);

        // add conditions
        $condition = $this->preparePriceCondition($value, $joinAliasPrice, Operators::IS_EMPTY);
        $exprNull  = $this->qb->expr()->isNull($joinAliasPrice . '.id');
        $exprOr    = $this->qb->expr()->orX($condition, $exprNull);
        $this->qb->andWhere($exprOr);
    }

    /**
     * @param AttributeInterface $attribute
     * @param array              $value
     * @param string             $operator
     * @param string             $locale
     * @param string             $scope
     */
    protected function addNonEmptyFilter(
        AttributeInterface $attribute,
        array $value,
        $operator,
        $locale = null,
        $scope = null
    ) {
        $backendType = $attribute->getBackendType();
        $joinAlias   = $this->getUniqueAlias('filter' . $attribute->getCode(), true);

        // join to value
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);

        $this->qb->innerJoin(
            $this->qb->getRootAlias() . '.values',
            $joinAlias,
            'WITH',
            $condition
        );

        $joinAliasPrice = $this->getUniqueAlias('filterP' . $attribute->getCode());
        $condition      = $this->preparePriceCondition($value, $joinAliasPrice, $operator);

        $this->qb->innerJoin($joinAlias . '.' .$backendType, $joinAliasPrice, 'WITH', $condition);
    }

    /**
     * Prepare price condition to join
     *
     * @param array  $value
     * @param string $joinAlias
     * @param string $operator
     *
     * @return string
     */
    protected function preparePriceCondition(array $value, $joinAlias, $operator)
    {
        $valueField     = sprintf('%s.%s', $joinAlias, 'data');
        $valueCondition = $this->prepareCriteriaCondition($valueField, $operator, $value['data']);

        $currencyField     = sprintf('%s.%s', $joinAlias, 'currency');
        $currencyCondition = $this->prepareCriteriaCondition($currencyField, '=', $value['currency']);

        return sprintf('%s AND %s', $currencyCondition, $valueCondition);
    }

    /**
     * @param AttributeInterface $attribute
     * @param mixed              $data
     */
    protected function checkValue(AttributeInterface $attribute, $data)
    {
        if (!is_array($data)) {
            throw InvalidArgumentException::arrayExpected($attribute->getCode(), 'filter', 'price', gettype($data));
        }

        if (!array_key_exists('data', $data)) {
            throw InvalidArgumentException::arrayKeyExpected(
                $attribute->getCode(),
                'data',
                'filter',
                'price',
                print_r($data, true)
            );
        }

        if (!array_key_exists('currency', $data)) {
            throw InvalidArgumentException::arrayKeyExpected(
                $attribute->getCode(),
                'currency',
                'filter',
                'price',
                print_r($data, true)
            );
        }

        if (!is_numeric($data['data']) && null !== $data['data']) {
            throw InvalidArgumentException::arrayNumericKeyExpected(
                $attribute->getCode(),
                'data',
                'filter',
                'price',
                gettype($data['data'])
            );
        }

        if (!is_string($data['currency'])) {
            throw InvalidArgumentException::arrayStringKeyExpected(
                $attribute->getCode(),
                'currency',
                'filter',
                'price',
                gettype($data['currency'])
            );
        }

        if (!in_array($data['currency'], $this->currencyManager->getActiveCodes())) {
            throw InvalidArgumentException::arrayInvalidKey(
                $attribute->getCode(),
                'currency',
                'The currency does not exist',
                'filter',
                'price',
                $data['currency']
            );
        }
    }
}
