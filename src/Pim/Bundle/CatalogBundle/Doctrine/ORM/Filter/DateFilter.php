<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;

/**
 * Date filter
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateFilter extends AbstractAttributeFilter implements FieldFilterInterface, AttributeFilterInterface
{
    /** @var array */
    protected $supportedAttributes;

    /** @var array */
    protected $supportedFields;

    /**
     * Instantiate the base filter
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

        $joinAlias    = $this->getUniqueAlias('filter' . $attribute->getCode());
        $backendField = sprintf('%s.%s', $joinAlias, $attribute->getBackendType());

        if ($operator === Operators::IS_EMPTY) {
            $this->qb->leftJoin(
                $this->qb->getRootAlias() . '.values',
                $joinAlias,
                'WITH',
                $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope)
            );
            $this->qb->andWhere($this->prepareCriteriaCondition($backendField, $operator, $value));
        } elseif ($operator === Operators::NOT_BETWEEN) {
            $this->qb->leftJoin(
                $this->qb->getRootAlias() . '.values',
                $joinAlias,
                'WITH',
                $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope)
            );
            $this->qb->andWhere(
                $this->qb->expr()->orX(
                    $this->qb->expr()->lt($backendField, $this->getDateLiteralExpr($value[0], true)),
                    $this->qb->expr()->gt($backendField, $this->getDateLiteralExpr($value[1], true))
                )
            );
        } else {
            $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);
            $condition .= ' AND ' . $this->prepareCriteriaCondition($backendField, $operator, $value);
            $this->qb->innerJoin(
                $this->qb->getRootAlias() . '.values',
                $joinAlias,
                'WITH',
                $condition
            );
        }

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

        $field = current($this->qb->getRootAliases()) . '.' . $field;

        switch ($operator) {
            case Operators::BETWEEN:
                $this->qb->andWhere(
                    $this->qb->expr()->andX(
                        $this->qb->expr()->gt($field, $this->getDateLiteralExpr($value[0])),
                        $this->qb->expr()->lt($field, $this->getDateLiteralExpr($value[1], true))
                    )
                );
                break;

            case Operators::NOT_BETWEEN:
                $this->qb->andWhere(
                    $this->qb->expr()->orX(
                        $this->qb->expr()->lt($field, $this->getDateLiteralExpr($value[0])),
                        $this->qb->expr()->gt($field, $this->getDateLiteralExpr($value[1], true))
                    )
                );
                break;

            case Operators::GREATER_THAN:
                $this->qb->andWhere($this->qb->expr()->gt($field, $this->getDateLiteralExpr($value, true)));
                break;

            case Operators::LOWER_THAN:
                $this->qb->andWhere($this->qb->expr()->lt($field, $this->getDateLiteralExpr($value)));
                break;

            case Operators::EQUALS:
                $this->qb->andWhere(
                    $this->qb->expr()->andX(
                        $this->qb->expr()->gt($field, $this->getDateLiteralExpr($value)),
                        $this->qb->expr()->lt($field, $this->getDateLiteralExpr($value, true))
                    )
                );
                break;

            case Operators::IS_EMPTY:
                $this->qb->andWhere($this->qb->expr()->isNull($field));
                break;
        }

        return $this;
    }

    /**
     * Get the literal expression of the date
     *
     * @param string $data
     * @param bool   $endOfDay
     *
     * @return \Doctrine\ORM\Query\Expr\Literal
     */
    protected function getDateLiteralExpr($data, $endOfDay = false)
    {
        return $this->qb->expr()->literal($this->getDateValue($data, $endOfDay));
    }

    /**
     * Get the date formatted from data
     *
     * @param \DateTime|string $data
     * @param bool             $endOfDay
     *
     * @return string
     */
    protected function getDateValue($data, $endOfDay = false)
    {
        if ($data instanceof \DateTime && true === $endOfDay) {
            $data->setTime(23, 59, 59);
        } elseif (!$data instanceof \DateTime && true === $endOfDay) {
            $data = sprintf('%s 23:59:59', $data);
        }

        return $data instanceof \DateTime ? $data->format('Y-m-d H:i:s') : $data;
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
}
