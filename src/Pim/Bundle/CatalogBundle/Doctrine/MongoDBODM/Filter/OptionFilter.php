<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterHelper;
use Pim\Bundle\CatalogBundle\Doctrine\Query\Operators;
use Pim\Bundle\CatalogBundle\Doctrine\Common\ObjectIdResolverInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Simple option filter for MongoDB implementation
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class OptionFilter extends AbstractFilter implements AttributeFilterInterface
{
    /** @var array */
    protected $supportedAttributes;

    /** @var ObjectIdResolverInterface */
    protected $objectIdResolver;

    /**
     * Instanciate the filter
     *
     * @param ObjectIdResolverInterface $objectIdResolver
     * @param array                     $supportedAttributes
     * @param array                     $supportedOperators
     */
    public function __construct(
        ObjectIdResolverInterface $objectIdResolver,
        array $supportedAttributes = [],
        array $supportedOperators = []
    ) {
        $this->objectIdResolver    = $objectIdResolver;
        $this->supportedAttributes = $supportedAttributes;
        $this->supportedOperators  = $supportedOperators;
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
        $this->checkValue($options['field'], $value);

        if (FieldFilterHelper::getProperty($options['field']) === FieldFilterHelper::CODE_PROPERTY) {
            $value = $this->objectIdResolver->getIdsFromCodes('option', $value);
        }

        $mongoField = sprintf(
            '%s.%s.id',
            ProductQueryUtility::NORMALIZED_FIELD,
            ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $locale, $scope)
        );

        $this->applyFilter($operator, $value, $mongoField, $options);

        return $this;
    }

    /**
     * Check if value is valid
     *
     * @param string $field
     * @param mixed  $values
     */
    protected function checkValue($field, $values)
    {
        FieldFilterHelper::checkArray($field, $values, 'option');

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, 'option');
        }
    }

    /**
     * Apply the filter to the query with the given operator
     *
     * @param string       $operator
     * @param string|array $value
     * @param string       $field
     */
    protected function applyFilter($operator, $value, $field)
    {
        if (Operators::IS_EMPTY === $operator) {
            $expr = $this->qb->expr()->field($field)->exists(false);
            $this->qb->addAnd($expr);
        } else {
            $value = array_map('intval', $value);
            $expr = $this->qb->expr()->field($field)->in($value);
            $this->qb->addAnd($expr);
        }
    }
}
