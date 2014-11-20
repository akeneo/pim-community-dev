<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
use Pim\Bundle\CatalogBundle\Doctrine\Query\Operators;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Date filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateFilter extends AbstractFilter implements AttributeFilterInterface, FieldFilterInterface
{
    /** @var array */
    protected $supportedAttributes;

    /** @var array */
    protected $supportedFields;

    /**
     * Instanciate the filter
     *
     * @param array $supportedAttributes
     * @param array $supportedFields
     * @param array $supportedOperators
     */
    public function __construct(
        array $supportedAttributes = [],
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
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
    public function addAttributeFilter(AttributeInterface $attribute, $operator, $value, $locale = null, $scope = null)
    {
        $this->checkValues($attribute->getCode(), $value);

        $field = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $locale, $scope);
        $this->addFieldFilter($field, $operator, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null)
    {
        $this->checkValues($field, $value);

        $field = sprintf('%s.%s', ProductQueryUtility::NORMALIZED_FIELD, $field);

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

        return $this;
    }

    /**
     * Get timestamp from data
     *
     * @param \DateTime|string $data
     * @param boolean          $endOfDay
     *
     * @return integer
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
     * Check if values are valid
     *
     * @param string $type
     * @param mixed  $value
     *
     * todo: find a better way to check this
     */
    protected function checkValues($type, $value)
    {
        if (is_array($value)) {
            if (count($value) !== 2 || (!is_string($value[0]) && !is_string($value[1]))) {
                throw InvalidArgumentException::stringExpected($type, 'date filter', 'date');
            }

            //TODO: maybe find another way to do this ?
            $this->checkDateFormat($type, $value[0]);
            $this->checkDateFormat($type, $value[1]);
        } elseif (is_string($value)) {
            if ('' !== $value) {
                $this->checkDateFormat($type, $value);
            }
        } elseif (null !== $value) {
            throw InvalidArgumentException::expected(
                $type,
                'array or string',
                'date filter',
                'date'
            );
        }
    }

    /**
     * Check if the date format is valid
     *
     * @param string $type
     * @param string $value
     */
    protected function checkDateFormat($type, $value)
    {
        $dateValues = explode('-', $value);

        if (
            count($dateValues) !== 3
            || (!is_numeric($dateValues[0]) || !is_numeric($dateValues[1]) || !is_numeric($dateValues[2]))
            || !checkdate($dateValues[1], $dateValues[2], $dateValues[0])
        ) {
            throw InvalidArgumentException::expected(
                $type,
                'a string with the format yyyy-mm-dd',
                'setter',
                'date'
            );
        }
    }
}
