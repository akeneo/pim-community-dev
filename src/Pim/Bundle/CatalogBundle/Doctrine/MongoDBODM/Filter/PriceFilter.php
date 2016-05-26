<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\ProductQueryUtility;
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

    /** @var array */
    protected $supportedAttributes;

    /**
     * @param AttributeValidatorHelper    $attrValidatorHelper
     * @param CurrencyRepositoryInterface $currencyRepository
     * @param array                       $supportedAttributes
     * @param array                       $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        CurrencyRepositoryInterface $currencyRepository,
        array $supportedAttributes = [],
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
        $this->currencyRepository  = $currencyRepository;
        $this->supportedAttributes = $supportedAttributes;
        $this->supportedOperators  = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array($attribute->getAttributeType(), $this->supportedAttributes);
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

        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $value['data'] = (float) $value['data'];
        }

        $field = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $locale, $scope);
        $field = sprintf(
            '%s.%s.%s.data',
            ProductQueryUtility::NORMALIZED_FIELD,
            $field,
            $value['currency']
        );

        $this->applyFilter($field, $operator, $value['data']);

        return $this;
    }

    /**
     * Apply the filter to the query with the given operator
     *
     * @param string $field
     * @param string $operator
     * @param float  $data
     */
    protected function applyFilter($field, $operator, $data)
    {
        switch ($operator) {
            case Operators::EQUALS:
                $this->qb->field($field)->equals($data);
                break;
            case Operators::NOT_EQUAL:
                $this->qb->field($field)->exists(true);
                $this->qb->field($field)->notEqual($data);
                break;
            case Operators::LOWER_THAN:
                $this->qb->field($field)->lt($data);
                break;
            case Operators::LOWER_OR_EQUAL_THAN:
                $this->qb->field($field)->lte($data);
                break;
            case Operators::GREATER_THAN:
                $this->qb->field($field)->gt($data);
                break;
            case Operators::GREATER_OR_EQUAL_THAN:
                $this->qb->field($field)->gte($data);
                break;
            case Operators::IS_EMPTY:
                $this->qb->field($field)->exists(false);
                break;
            case Operators::IS_NOT_EMPTY:
                $this->qb->field($field)->exists(true);
                break;
        }
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

        if (null !== $data['data'] && !is_int($data['data']) && !is_float($data['data'])) {
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
