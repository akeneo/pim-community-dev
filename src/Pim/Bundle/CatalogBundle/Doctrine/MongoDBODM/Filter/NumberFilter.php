<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
use Pim\Bundle\CatalogBundle\Doctrine\Query\Operators;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Number filter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberFilter implements AttributeFilterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var array */
    protected $supportedAttributes;

    /** @var array */
    protected $supportedOperators;

    /**
     * Instanciate the filter
     *
     * @param array $supportedAttributes
     * @param array $supportedOperators
     */
    public function __construct(
        array $supportedAttributes = [],
        array $supportedOperators = []
    ) {
        $this->supportedAttributes = $supportedAttributes;
        $this->supportedOperators  = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryBuilder($queryBuilder)
    {
        if (!($queryBuilder instanceof QueryBuilder)) {
            throw new \InvalidArgumentException(
                'Query builder should be an instance of Doctrine\ODM\MongoDB\Query\Builder'
            );
        }

        $this->qb = $queryBuilder;
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
    public function supportsOperator($operator)
    {
        return in_array($operator, $this->supportedOperators);
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

        $field = sprintf('%s.%s', ProductQueryUtility::NORMALIZED_FIELD, $field);

        if (Operators::IS_EMPTY === $operator) {
            $this->qb->field($field)->exists(false);
        } elseif (Operators::NOT_IN_LIST === $operator) {
            $this->qb->field($field)->in($value);
        } else {
            $this->qb->field($field)->equals($value);
        }

        return $this;
    }
}
