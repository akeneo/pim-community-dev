<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

/**
 * Base filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseFilter implements AttributeFilterInterface, FieldFilterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var CatalogContext */
    protected $context;

    /** @var array */
    protected $supportedAttributes;

    /** @var array */
    protected $supportedOperators;

    /**
     * Instanciate the filter
     *
     * @param CatalogContext $context
     * @param array          $extraSupportedAttributes
     * @param array          $extraSupportedOperators
     */
    public function __construct(
        CatalogContext $context,
        array $extraSupportedAttributes = [],
        array $extraSupportedOperators = []
    ) {
        $this->context = $context;
        $this->supportedAttributes = array_merge(
            [
                'pim_catalog_identifier',
                'pim_catalog_text',
                'pim_catalog_textarea',
                'pim_catalog_number',
                'pim_catalog_boolean'
            ],
            $extraSupportedAttributes
        );
        $this->supportedOperators = array_merge(
            ['IN', 'NOT IN', '=', '<', '<=', '>', '>=', 'EMPTY', 'LIKE'],
            $extraSupportedOperators
        );
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
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AbstractAttribute $attribute)
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
    public function addAttributeFilter(AbstractAttribute $attribute, $operator, $value)
    {
        $field = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $this->context);
        $this->addFieldFilter($field, $operator, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value)
    {
        $field = sprintf('%s.%s', ProductQueryUtility::NORMALIZED_FIELD, $field);

        if ('EMPTY' === $operator) {
            $this->qb->field($field)->exists(false);
        } elseif ('IN' === $operator) {
            $this->qb->field($field)->in($value);
        } else {
            if (strpos($value, '/') !== false) {
                $value = new \MongoRegex($value);
            }
            $this->qb->field($field)->equals($value);
        }

        return $this;
    }
}
