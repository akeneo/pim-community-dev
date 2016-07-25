<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Price filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var CurrencyRepositoryInterface */
    protected $currencyRepository;

    /**
     * @param AttributeValidatorHelper    $attrValidatorHelper
     * @param CurrencyRepositoryInterface $currencyRepository
     * @param array                       $supportedAttributeTypes
     * @param array                       $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        CurrencyRepositoryInterface $currencyRepository,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
        $this->currencyRepository  = $currencyRepository;
        $this->supportedAttributeTypes = $supportedAttributeTypes;
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

        if (Operators::IS_EMPTY === $operator || Operators::IS_NOT_EMPTY === $operator) {
            $this->addEmptyTypeFilter($attribute, $value, $operator, $locale, $scope);
        } else {
            $this->checkValue($attribute, $value);
            $this->addFilter($attribute, $value, $operator, $locale, $scope);
        }

        return $this;
    }

    /**
     * @param AttributeInterface $attribute
     * @param array              $value
     * @param string             $operator
     * @param string             $locale
     * @param string             $scope
     */
    protected function addEmptyTypeFilter(
        AttributeInterface $attribute,
        array $value,
        $operator,
        $locale = null,
        $scope = null
    ) {
        $backendType = $attribute->getBackendType();
        $joinAlias   = $this->getUniqueAlias('filter' . $attribute->getCode());

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

        $priceCondition   = $this->preparePriceCondition($value, $joinAliasPrice, $operator);
        $priceIdCondition = $this->prepareCriteriaCondition($joinAliasPrice . '.id', $operator, null);
        if (Operators::IS_NOT_EMPTY === $operator) {
            $where = $this->qb->expr()->andX($priceCondition, $priceIdCondition);
        } else {
            $where = $this->qb->expr()->orX($priceCondition, $priceIdCondition);
        }
        $this->qb->andWhere($where);
    }

    /**
     * @param AttributeInterface $attribute
     * @param array              $value
     * @param string             $operator
     * @param string             $locale
     * @param string             $scope
     */
    protected function addFilter(
        AttributeInterface $attribute,
        array $value,
        $operator,
        $locale = null,
        $scope = null
    ) {
        $backendType = $attribute->getBackendType();
        $joinAlias   = $this->getUniqueAlias('filter' . $attribute->getCode());

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

        if (!in_array($data['currency'], $this->currencyRepository->getActivatedCurrencyCodes())) {
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
