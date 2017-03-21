<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Bundle\CatalogBundle\ProductQueryUtility;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * String filter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StringFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var OptionsResolver */
    protected $resolver;

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

        $this->resolver = new OptionsResolver();
        $this->configureOptions($this->resolver);
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
        try {
            $options = $this->resolver->resolve($options);
        } catch (\Exception $e) {
            throw InvalidPropertyException::expectedFromPreviousException(
                $attribute->getCode(),
                static::class,
                $e
            );
        }

        $this->checkLocaleAndScope($attribute, $locale, $scope);

        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($options['field'], $value);
        }

        $field = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $locale, $scope);
        $field = sprintf('%s.%s', ProductQueryUtility::NORMALIZED_FIELD, $field);

        $this->applyFilter($field, $operator, $value);

        return $this;
    }

    /**
     * Apply the filter to the query with the given operator
     *
     * @param string       $field
     * @param string       $operator
     * @param string|array $value
     */
    protected function applyFilter($field, $operator, $value)
    {
        switch ($operator) {
            case Operators::IS_EMPTY:
                $this->qb->field($field)->exists(false);
                break;
            case Operators::IS_NOT_EMPTY:
                $this->qb->field($field)->exists(true);
                break;
            case Operators::IN_LIST:
                $this->qb->field($field)->in($value);
                break;
            case Operators::NOT_EQUAL:
                $this->qb->field($field)->exists(true);
                $this->qb->field($field)->notEqual($value);
                break;
            case Operators::DOES_NOT_CONTAIN:
                $value = $this->prepareValue($operator, $value);
                $this->qb->addAnd(
                    $this->qb->expr()
                        ->addOr($this->qb->expr()->field($field)->exists(false))
                        ->addOr($this->qb->expr()->field($field)->equals($value))
                );
                break;
            default:
                $value = $this->prepareValue($operator, $value);
                $this->qb->field($field)->equals($value);
        }
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
                $value = new \MongoRegex(sprintf('/^%s/i', preg_quote($value)));
                break;
            case Operators::ENDS_WITH:
                $value = new \MongoRegex(sprintf('/%s$/i', preg_quote($value)));
                break;
            case Operators::CONTAINS:
                $value = new \MongoRegex(sprintf('/%s/i', preg_quote($value)));
                break;
            case Operators::DOES_NOT_CONTAIN:
                $value = new \MongoRegex(sprintf('/^((?!%s).)*$/i', preg_quote($value)));
                break;
        }

        return $value;
    }

    /**
     * Check if value is valid
     *
     * @param string $field
     * @param mixed  $value
     */
    protected function checkValue($field, $value)
    {
        if (is_array($value)) {
            foreach ($value as $scalarValue) {
                $this->checkScalarValue($field, $scalarValue);
            }
        } else {
            $this->checkScalarValue($field, $value);
        }
    }

    /**
     * @param string $field
     * @param mixed  $value
     */
    protected function checkScalarValue($field, $value)
    {
        if (!is_string($value) && null !== $value) {
            throw InvalidPropertyTypeException::stringExpected($field, static::class, $value);
        }
    }

    /**
     * Configure the option resolver
     *
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['field']);
        $resolver->setDefined(['locale', 'scope']);
    }
}
