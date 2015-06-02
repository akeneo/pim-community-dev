<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;

/**
 * Date filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateFilter extends AbstractAttributeFilter implements AttributeFilterInterface, FieldFilterInterface
{
    /** @var array */
    protected $supportedAttributes;

    /** @var array */
    protected $supportedFields;

    /**
     * Instanciate the filter
     *
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param array                    $supportedAttributes
     * @param array                    $supportedFields
     * @param array                    $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        array $supportedAttributes = [],
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
        $this->supportedAttributes = $supportedAttributes;
        $this->supportedFields     = $supportedFields;
        $this->supportedOperators  = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsField($field)
    {
        return in_array($field, $this->supportedFields);
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
        $this->checkLocaleAndScope($attribute, $locale, $scope, 'date');

        if (Operators::IS_EMPTY === $operator) {
            $value = null;
        } else {
            $value = $this->formatValues($attribute->getCode(), $value);
        }

        $field = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $locale, $scope);
        $this->addFieldFilter($field, $operator, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        if (Operators::IS_EMPTY === $operator) {
            $value = null;
        } else {
            $value = $this->formatValues($field, $value);
        }

        $field = sprintf('%s.%s', ProductQueryUtility::NORMALIZED_FIELD, $field);
        $this->applyFilter($value, $field, $operator);

        return $this;
    }

    /**
     * Get timestamp from data
     *
     * @param \DateTime|string $data
     * @param bool             $endOfDay
     *
     * @return int
     */
    protected function getTimestamp($data, $endOfDay = false)
    {
        if ($data instanceof \DateTime && true === $endOfDay) {
            $data->setTime(23, 59, 59);
        } elseif (!$data instanceof \DateTime && true === $endOfDay) {
            $data = sprintf('%s 23:59:59', $data);
        }

        return $data instanceof \DateTime ? $data->getTimestamp() : strtotime($data);
    }

    /**
     * Format values to string or array of strings
     *
     * @param string $type
     * @param mixed  $value
     *
     * @return mixed $value
     */
    protected function formatValues($type, $value)
    {
        if (is_array($value) && 2 !== count($value)) {
            throw InvalidArgumentException::expected(
                $type,
                'array with 2 elements, string or \Datetime',
                'filter',
                'date',
                print_r($value, true)
            );
        }

        if (is_array($value)) {
            $tmpValues = [];
            foreach ($value as $tmp) {
                $tmpValues[] = $this->formatSingleValue($type, $tmp);
            }
            $value = $tmpValues;
        } else {
            $value = $this->formatSingleValue($type, $value);
        }

        return $value;
    }

    /**
     * @param string $type
     * @param mixed  $value
     *
     * @return string
     */
    protected function formatSingleValue($type, $value)
    {
        if ($value instanceof \DateTime) {
            $value = $value->format('Y-m-d');
        } elseif (is_string($value)) {
            $this->validateDateFormat($type, $value);
        } elseif (null !== $value) {
            throw InvalidArgumentException::expected(
                $type,
                'array with 2 elements, string or \Datetime',
                'filter',
                'date',
                print_r($value, true)
            );
        }

        return $value;
    }

    /**
     * Check if the date format is valid
     *
     * @param string $type
     * @param string $value
     */
    protected function validateDateFormat($type, $value)
    {
        $dateValues = explode('-', $value);

        if (count($dateValues) !== 3
            || (!is_numeric($dateValues[0]) || !is_numeric($dateValues[1]) || !is_numeric($dateValues[2]))
            || !checkdate($dateValues[1], $dateValues[2], $dateValues[0])
        ) {
            throw InvalidArgumentException::expected(
                $type,
                'a string with the format yyyy-mm-dd',
                'filter',
                'date',
                $value
            );
        }
    }

    /**
     * Apply the filter to the query with the given operator
     *
     * @param mixed  $value
     * @param string $field
     * @param string $operator
     */
    protected function applyFilter($value, $field, $operator)
    {
        switch ($operator) {
            case Operators::BETWEEN:
                $this->qb->field($field)->gte($this->getTimestamp($value[0]));
                $this->qb->field($field)->lte($this->getTimestamp($value[1], true));
                break;
            case Operators::NOT_BETWEEN:
                $this->qb->addAnd(
                    $this->qb->expr()
                        ->addOr($this->qb->expr()->field($field)->lte($this->getTimestamp($value[0])))
                        ->addOr($this->qb->expr()->field($field)->gte($this->getTimestamp($value[1], true)))
                );
                break;
            case Operators::GREATER_THAN:
                $this->qb->field($field)->gt($this->getTimestamp($value, true));
                break;
            case Operators::LOWER_THAN:
                $this->qb->field($field)->lt($this->getTimestamp($value));
                break;
            case Operators::EQUALS:
                $this->qb->field($field)->gte($this->getTimestamp($value));
                $this->qb->field($field)->lte($this->getTimestamp($value, true));
                break;
            case Operators::IS_EMPTY:
                $this->qb->field($field)->exists(false);
                break;
        }
    }
}
