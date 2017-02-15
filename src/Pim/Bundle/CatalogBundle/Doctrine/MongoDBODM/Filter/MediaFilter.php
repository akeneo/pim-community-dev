<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Bundle\CatalogBundle\ProductQueryUtility;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Media filter
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /**
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param array                    $supportedAttributeTypes
     * @param array                    $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
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
        }

        $field = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $locale, $scope);
        $field = sprintf('%s.%s.originalFilename', ProductQueryUtility::NORMALIZED_FIELD, $field);

        switch ($operator) {
            case Operators::NOT_EQUAL:
                $this->qb->field($field)->exists(true);
                $this->qb->field($field)->notEqual($value);
                break;
            case Operators::IS_NOT_EMPTY:
                $this->qb->field($field)->exists(true);
                break;
            case Operators::IS_EMPTY:
                $this->qb->field($field)->exists(false);
                break;
            default:
                $value = $this->prepareValue($operator, $value);
                $this->qb->field($field)->equals($value);
        }

        return $this;
    }

    /**
     * Prepare value of the filter
     *
     * @param string|array $operator
     * @param string|array $value
     *
     * @return string
     */
    protected function prepareValue($operator, $value)
    {
        switch ($operator) {
            case Operators::EQUALS:
                $value = new \MongoRegex(sprintf('/^%s$/i', $value));
                break;
            case Operators::STARTS_WITH:
                $value = new \MongoRegex(sprintf('/^%s/i', $value));
                break;
            case Operators::ENDS_WITH:
                $value = new \MongoRegex(sprintf('/%s$/i', $value));
                break;
            case Operators::CONTAINS:
                $value = new \MongoRegex(sprintf('/%s/i', $value));
                break;
            case Operators::DOES_NOT_CONTAIN:
                $value = new \MongoRegex(sprintf('/^((?!%s).)*$/i', $value));
                break;
        }

        return $value;
    }

    /**
     * @param AttributeInterface $attribute
     * @param mixed              $value
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkValue(AttributeInterface $attribute, $value)
    {
        if (!is_string($value)) {
            throw InvalidPropertyTypeException::stringExpected(
                $attribute->getCode(),
                static::class,
                $value
            );
        }
    }
}
