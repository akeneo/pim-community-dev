<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Bundle\CatalogBundle\ProductQueryUtility;
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
        $this->currencyRepository = $currencyRepository;
        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->supportedOperators = $supportedOperators;
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
        $this->checkLocaleAndScope($attribute, $locale, $scope);

        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($attribute, $value);
            $value['amount'] = (float) $value['amount'];
        }

        $field = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $locale, $scope);
        $field = sprintf(
            '%s.%s.%s.data',
            ProductQueryUtility::NORMALIZED_FIELD,
            $field,
            $value['currency']
        );

        $this->applyFilter($field, $operator, $value['amount']);

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
            throw InvalidPropertyTypeException::arrayExpected($attribute->getCode(), static::class, $data);
        }

        if (!array_key_exists('amount', $data)) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $attribute->getCode(),
                'amount',
                static::class,
                $data
            );
        }

        if (!array_key_exists('currency', $data)) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $attribute->getCode(),
                'currency',
                static::class,
                $data
            );
        }

        if (null !== $data['amount'] && !is_numeric($data['amount'])) {
            throw InvalidPropertyTypeException::validArrayStructureExpected(
                $attribute->getCode(),
                sprintf('key "amount" has to be a numeric, "%s" given', gettype($data['amount'])),
                static::class,
                $data
            );
        }

        if (!is_string($data['currency'])) {
            throw InvalidPropertyTypeException::validArrayStructureExpected(
                $attribute->getCode(),
                sprintf('key "currency" has to be a string, "%s" given', gettype($data['currency'])),
                static::class,
                $data
            );
        }

        if (!in_array($data['currency'], $this->currencyRepository->getActivatedCurrencyCodes())) {
            throw InvalidPropertyException::validEntityCodeExpected(
                $attribute->getCode(),
                'currency',
                'The currency does not exist',
                static::class,
                $data['currency']
            );
        }
    }
}
