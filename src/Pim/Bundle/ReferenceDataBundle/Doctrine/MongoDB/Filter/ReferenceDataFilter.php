<?php

namespace Pim\Bundle\ReferenceDataBundle\Doctrine\MongoDB\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\AbstractAttributeFilter;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterHelper;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Pim\Component\ReferenceData\ConfigurationRegistry;

/**
 * Reference data filter
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var AttributeValidatorHelper */
    protected $attrValidatorHelper;

    /** @var ConfigurationRegistry */
    protected $registry;

    /** @var array */
    protected $supportedAttributes;

    /**
     * Instanciate the base filter
     *
     * @param array $supportedAttributes
     * @param array $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        ConfigurationRegistry $registry,
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
        $this->registry = $registry;
        $this->supportedOperators  = $supportedOperators;
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
        $this->checkLocaleAndScope($attribute, $locale, $scope, 'number');

        if (Operators::IS_EMPTY !== $operator) {
            $this->checkValue($attribute, $value);
        }

        $field = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $locale, $scope);
        $field = sprintf(
            '%s.%s.id',
            ProductQueryUtility::NORMALIZED_FIELD,
            ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $locale, $scope)
        );

        $this->applyFilter($operator, $value, $field);

        return $this;
    }

    /**
     * Apply the filter to the query with the given operator
     *
     * @param string       $operator
     * @param null|integer $value
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

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        $referenceDataName = $attribute->getReferenceDataName();

        return null !== $referenceDataName && null !== $this->registry->get($referenceDataName) ? true : false;
    }

    /**
     * Check if value is valid
     *
     * @param string $field
     * @param mixed  $values
     */
    protected function checkValue($field, $values)
    {
        FieldFilterHelper::checkArray($field->getId(), $values, 'reference_data');

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field->getId(), $value, 'reference_data');
        }
    }
}
