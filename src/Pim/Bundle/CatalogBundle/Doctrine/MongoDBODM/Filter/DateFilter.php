<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\ProductQueryUtility;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

/**
 * Date filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    const DATETIME_FORMAT = 'Y-m-d';

    /**
     * @param ChannelRepositoryInterface $channelRepository
     * @param LocaleRepositoryInterface  $localeRepository
     * @param array                      $supportedAttributeTypes
     * @param array                      $supportedOperators
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        parent::__construct($channelRepository, $localeRepository);

        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->supportedOperators      = $supportedOperators;
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
        if (Operators::IS_EMPTY === $operator || Operators::IS_NOT_EMPTY === $operator) {
            $value = null;
        } else {
            $value = $this->formatValues($attribute->getCode(), $value);
        }

        $normalizedFields = $this->getNormalizedValueFieldsFromAttribute($attribute, $locale, $scope);
        $fields = [];

        foreach ($normalizedFields as $normalizedField) {
            $fields[] = sprintf('%s.%s', ProductQueryUtility::NORMALIZED_FIELD, $normalizedField);
        }

        $this->applyFilters($fields, $operator, $value);

        return $this;
    }

    /**
     * Apply the filters to the query with the given operator
     *
     * @param array  $fields
     * @param string $operator
     * @param mixed  $value
     */
    protected function applyFilters(array $fields, $operator, $value)
    {
        switch ($operator) {
            case Operators::BETWEEN:
                foreach ($fields as $field) {
                    $this->qb->field($field)->gte($value[0]);
                    $this->qb->field($field)->lte($value[1]);
                }
                break;
            case Operators::NOT_BETWEEN:
                foreach ($fields as $field) {
                    $this->qb->addAnd(
                        $this->qb->expr()
                            ->addOr($this->qb->expr()->field($field)->lt($value[0]))
                            ->addOr($this->qb->expr()->field($field)->gt($value[1]))
                    );
                }
                break;
            case Operators::GREATER_THAN:
                foreach ($fields as $field) {
                    $expr = $this->qb->expr()->field($field)->gt($value);
                    $this->qb->addOr($expr);
                }
                break;
            case Operators::LOWER_THAN:
                foreach ($fields as $field) {
                    $expr = $this->qb->expr()->field($field)->lt($value);
                    $this->qb->addOr($expr);
                }
                break;
            case Operators::EQUALS:
                foreach ($fields as $field) {
                    $expr = $this->qb->expr()->field($field)->equals($value);
                    $this->qb->addOr($expr);
                }
                break;
            case Operators::NOT_EQUAL:
                foreach ($fields as $field) {
                    $this->qb->field($field)->exists(true);
                    $this->qb->field($field)->notEqual($value);
                }
                break;
            case Operators::IS_EMPTY:
                foreach ($fields as $field) {
                    $expr = $this->qb->expr()->field($field)->exists(false);
                    $this->qb->addAnd($expr);
                }
                break;
            case Operators::IS_NOT_EMPTY:
                foreach ($fields as $field) {
                    $expr = $this->qb->expr()->field($field)->exists(true);
                    $this->qb->addOr($expr);
                }
                break;
        }
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
     * @return int|null
     */
    protected function formatSingleValue($type, $value)
    {
        if (null === $value) {
            return $value;
        }

        if ($value instanceof \DateTime) {
            return $value->getTimestamp();
        }

        if (is_string($value)) {
            $dateTime = \DateTime::createFromFormat(static::DATETIME_FORMAT, $value);

            if (!$dateTime || 0 < $dateTime->getLastErrors()['warning_count']) {
                throw InvalidArgumentException::expected(
                    $type,
                    'a string with the format yyyy-mm-dd',
                    'filter',
                    'date',
                    $value
                );
            }

            $dateTime->setTime(0, 0, 0);

            return $dateTime->getTimestamp();
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
