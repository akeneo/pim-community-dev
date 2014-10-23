<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
use Pim\Bundle\CatalogBundle\Doctrine\Operators;
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
class DateFilter implements AttributeFilterInterface, FieldFilterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var array */
    protected $supportedAttributes;

    /** @var array */
    protected $supportedFields;

    /** @var array */
    protected $supportedOperators;

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
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryBuilder($queryBuilder)
    {
        $this->qb = $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsField($field)
    {
        return in_array(
            $field,
            $this->supportedFields
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array(
            $attribute->getAttributeType(),
            $this->supportedAttributes
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsOperator($operator)
    {
        return in_array(
            $operator,
            $this->supportedOperators
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getOperators()
    {
        return $this->supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(AttributeInterface $attribute, $operator, $value, array $context = [])
    {
        $field = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $context);
        $this->addFieldFilter($field, $operator, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, array $context = [])
    {
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
}
