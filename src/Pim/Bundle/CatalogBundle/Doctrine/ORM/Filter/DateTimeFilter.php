<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Datetime filter
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @param array $supportedFields
     * @param array $supportedOperators
     */
    public function __construct(array $supportedFields = [], array $supportedOperators = [])
    {
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        $value = Operators::IS_EMPTY === $operator ? null : $this->formatValues($field, $value);
        $field = current($this->qb->getRootAliases()) . '.' . $field;

        if (Operators::NOT_BETWEEN === $operator) {
            $this->qb->andWhere(
                $this->qb->expr()->orX(
                    $this->qb->expr()->lt($field, $this->qb->expr()->literal($value[0])),
                    $this->qb->expr()->gt($field, $this->qb->expr()->literal($value[1]))
                )
            );
        } else {
            $this->qb->andWhere($this->prepareCriteriaCondition($field, $operator, $value));
        }

        return $this;
    }

    /**
     * Format values to string or array of strings
     *
     * @param string $type
     * @param mixed  $value
     *
     * @throws InvalidArgumentException
     *
     * @return mixed $value
     */
    protected function formatValues($type, $value)
    {
        if (is_array($value) && 2 !== count($value)) {
            throw InvalidArgumentException::expected(
                $type,
                'array with 2 elements, string or \DateTime',
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
     * @throws InvalidArgumentException
     *
     * @return string
     */
    protected function formatSingleValue($type, $value)
    {
        if (null === $value) {
            return $value;
        }

        if ($value instanceof \DateTime) {
            $value->setTimezone(new \DateTimeZone('UTC'));
            return $value->format(static::DATETIME_FORMAT);
        }

        if (is_string($value)) {
            $dateTime = \DateTime::createFromFormat(static::DATETIME_FORMAT, $value);

            if (!$dateTime || 0 < $dateTime->getLastErrors()['warning_count']) {
                throw InvalidArgumentException::expected(
                    $type,
                    'a string with the format yyyy-mm-dd H:i:s',
                    'filter',
                    'date',
                    $value
                );
            }

            return $dateTime->format(static::DATETIME_FORMAT);
        }

        throw InvalidArgumentException::expected(
            $type,
            'array with 2 elements, string or \DateTime',
            'filter',
            'date',
            print_r($value, true)
        );
    }
}
