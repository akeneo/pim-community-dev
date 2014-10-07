<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;

/**
 * Multi options filter for MongoDB
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsFilter implements AttributeFilterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var CatalogContext */
    protected $context;

    /** @var array */
    protected $supportedOperators;

    /**
     * Instanciate the filter
     *
     * @param CatalogContext $context
     */
    public function __construct(CatalogContext $context)
    {
        $this->context = $context;
        $this->supportedOperators = ['IN'];
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
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return $attribute->getAttributeType() === 'pim_catalog_multiselect';
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
    public function addAttributeFilter(AttributeInterface $attribute, $operator, $value)
    {
        $field = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $this->context);
        $field = sprintf('%s.%s', ProductQueryUtility::NORMALIZED_FIELD, $field);
        $this->addFieldFilter($field, $operator, $value);

        return $this;
    }

    /**
     * @param string|array $field
     * @param string       $operator
     * @param string|array $value
     *
     * @return OptionsFilter
     */
    protected function addFieldFilter($field, $operator, $value)
    {
        $value = is_array($value) ? $value : [$value];

        if ($operator === 'NOT IN') {
            $this->qb->field($field)->notIn($value);
        } else {
            if (in_array('empty', $value)) {
                unset($value[array_search('empty', $value)]);

                $expr = $this->qb->expr()->field($field)->exists(false);
                $this->qb->addOr($expr);
            }

            if (count($value) > 0) {
                $value = array_map('intval', $value);
                $expr = $this->qb->expr()->field($field .'.id')->in($value);
                $this->qb->addOr($expr);
            }
        }

        return $this;
    }
}
