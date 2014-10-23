<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Base filter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StringFilter implements AttributeFilterInterface, FieldFilterInterface
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
        $this->supportedFields     = $supportedFields;
        $this->supportedOperators  = $supportedOperators;
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

        if ('EMPTY' === $operator) {
            $this->qb->field($field)->exists(false);
        } elseif ('IN' === $operator) {
            $this->qb->field($field)->in($value);
        } else {
            $value = $this->prepareValue($operator, $value);

            $this->qb->field($field)->equals($value);
        }

        return $this;
    }

    /**
     * Prepare value of the filter
     * @param string|array  $operator
     * @param string|array $value
     *
     * @return string
     */
    protected function prepareValue($operator, $value)
    {
        if ($operator === 'START WITH') {
            $value = new \MongoRegex(sprintf('/^%s/i', $value));
        } elseif ($operator === 'END WITH') {
            $value = new \MongoRegex(sprintf('/%s$/i', $value));
        } elseif ($operator === 'CONTAINS') {
            $value = new \MongoRegex(sprintf('/%s/i', $value));
        } elseif ($operator === 'DOES NOT CONTAIN') {
            $value = new \MongoRegex(sprintf('/^((?!%s).)*$/i', $value));
        }

        return $value;
    }
}
