<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\ProductQueryUtility;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Number filter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var array */
    protected $supportedAttributes;

    /**
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param array                    $supportedAttributes
     * @param array                    $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        array $supportedAttributes = [],
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
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
        $this->checkLocaleAndScope($attribute, $locale, $scope, 'number');

        if (null !== $value &&
            !is_int($value) &&
            !is_float($value)
        ) {
            throw InvalidArgumentException::numericExpected($attribute->getCode(), 'filter', 'number', gettype($value));
        }

        $field = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $locale, $scope);
        $field = sprintf('%s.%s', ProductQueryUtility::NORMALIZED_FIELD, $field);

        $this->applyFilter($operator, $value, $field);

        return $this;
    }

    /**
     * Apply the filter to the query with the given operator
     *
     * @param string   $operator
     * @param null|int $value
     * @param string   $field
     */
    protected function applyFilter($operator, $value, $field)
    {
        switch ($operator) {
            case Operators::IS_EMPTY:
                $this->qb->field($field)->exists(false);
                break;
            case Operators::IS_NOT_EMPTY:
                $this->qb->field($field)->exists(true);
                break;
            case Operators::EQUALS:
                $this->qb->field($field)->equals($value);
                break;
            case Operators::NOT_EQUAL:
                $this->qb->field($field)->exists(true);
                $this->qb->field($field)->notEqual($value);
                break;
            case Operators::LOWER_THAN:
                $this->qb->field($field)->lt($value);
                break;
            case Operators::GREATER_THAN:
                $this->qb->field($field)->gt($value);
                break;
            case Operators::LOWER_OR_EQUAL_THAN:
                $this->qb->field($field)->lte($value);
                break;
            case Operators::GREATER_OR_EQUAL_THAN:
                $this->qb->field($field)->gte($value);
                break;
        }
    }
}
