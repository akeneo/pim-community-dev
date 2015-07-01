<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
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

        if (Operators::IS_EMPTY !== $operator) {
            $value['data'] = (float) $value['data'];
        }

        $field = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $locale, $scope);
        $field = sprintf('%s.%s', ProductQueryUtility::NORMALIZED_FIELD, $field);
        $field = sprintf('%s.%s', $field, $value['currency']);
        $fieldData = sprintf('%s.data', $field);
        $this->applyFilter($operator, $fieldData, $value['data']);

        return $this;
    }

    /**
     * Apply the filter to the query with the given operator
     *
     * @param string $operator
     * @param string $fieldData
     * @param float  $data
     */
    protected function applyFilter($operator, $fieldData, $data)
    {
        switch ($operator) {
            case Operators::LOWER_THAN:
                $this->qb->field($fieldData)->lt($data);
                break;
            case Operators::LOWER_OR_EQUAL_THAN:
                $this->qb->field($fieldData)->lte($data);
                break;
            case Operators::GREATER_THAN:
                $this->qb->field($fieldData)->gt($data);
                break;
            case Operators::GREATER_OR_EQUAL_THAN:
                $this->qb->field($fieldData)->gte($data);
                break;
            case Operators::IS_EMPTY:
                $this->qb->field($fieldData)->equals(null);
                break;
            default:
                $this->qb->field($fieldData)->equals($data);
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
