<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;

/**
 * Media filter
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var array */
    protected $supportedAttributes;

    /**
     * Instanciate the base filter
     *
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
        $this->checkLocaleAndScope($attribute, $locale, $scope, 'media');

        $field = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $locale, $scope);
        $field = sprintf('%s.%s.originalFilename', ProductQueryUtility::NORMALIZED_FIELD, $field);

        if (Operators::IS_EMPTY !== $operator) {
            $this->checkValue($attribute, $value);
            $value = $this->prepareValue($operator, $value);
            $this->qb->field($field)->equals($value);
        } else {
            $this->qb->field($field)->exists(false);
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
            default:
                break;
        }

        return $value;
    }

    /**
     * @param AttributeInterface $attribute
     * @param mixed              $value
     */
    public function checkValue(AttributeInterface $attribute, $value)
    {
        if (!is_string($value)) {
            throw InvalidArgumentException::stringExpected(
                $attribute->getCode(),
                'filter',
                'media',
                gettype($value)
            );
        }
    }
}
