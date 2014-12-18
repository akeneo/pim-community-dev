<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterHelper;
use Pim\Bundle\CatalogBundle\Doctrine\Query\Operators;
use Pim\Bundle\CatalogBundle\Doctrine\Common\EntityIdResolverInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Multi options filter for MongoDB
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsFilter extends AbstractFilter implements AttributeFilterInterface
{
    /** @var array */
    protected $supportedAttributes;

    /** @var EntityIdResolverInterface */
    protected $entityIdResolver;

    /**
     * Instanciate the filter
     *
     * @param EntityIdResolverInterface $entityIdResolver
     * @param array                     $supportedAttributes
     * @param array                     $supportedOperators
     */
    public function __construct(
        EntityIdResolverInterface $entityIdResolver,
        array $supportedAttributes = [],
        array $supportedOperators = []
    ) {
        $this->entityIdResolver    = $entityIdResolver;
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
        if ($operator != Operators::IS_EMPTY) {
            $this->checkValue($options['field'], $value);
        }

        $value = !is_array($value) ? [$value] : $value;
        if (FieldFilterHelper::getProperty($options['field']) === FieldFilterHelper::CODE_PROPERTY) {
            $value = $this->entityIdResolver->getIdsFromCodes('option', $value);
        }

        $mongoField = sprintf(
            '%s.%s',
            ProductQueryUtility::NORMALIZED_FIELD,
            ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $locale, $scope)
        );

        $this->applyFilter($value, $operator, $mongoField);

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
     * @param array  $value
     * @param string $operator
     * @param string $field
     */
    protected function applyFilter(array $value, $operator, $field)
    {
        if ($operator === Operators::NOT_IN_LIST) {
            $this->qb->field($field)->notIn($value);
        } else {
            if (Operators::IS_EMPTY === $operator) {
                $expr = $this->qb->expr()->field($field)->exists(false);
                $this->qb->addAnd($expr);
            } else {
                $value = array_map('intval', $value);
                $expr = $this->qb->expr()->field($field.'.id')->in($value);
                $this->qb->addAnd($expr);
            }
        }
    }
}
