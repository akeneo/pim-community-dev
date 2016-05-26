<?php

namespace Pim\Bundle\ReferenceDataBundle\Doctrine\MongoDB\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\AbstractAttributeFilter;
use Pim\Bundle\CatalogBundle\ProductQueryUtility;
use Pim\Bundle\ReferenceDataBundle\Doctrine\ReferenceDataIdResolver;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;

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

    /** @var ConfigurationRegistryInterface */
    protected $registry;

    /** @var ReferenceDataIdResolver */
    protected $idsResolver;

    /** @var array */
    protected $supportedAttributes;

    /**
     * Instanciate the base filter
     *
     * @param AttributeValidatorHelper       $attrValidatorHelper
     * @param ConfigurationRegistryInterface $registry
     * @param ReferenceDataIdResolver        $idsResolver
     * @param array                          $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        ConfigurationRegistryInterface $registry,
        ReferenceDataIdResolver $idsResolver,
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
        $this->registry = $registry;
        $this->idsResolver = $idsResolver;
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
            $field = $options['field'];
            $this->checkValue($field, $value);

            if (FieldFilterHelper::CODE_PROPERTY === FieldFilterHelper::getProperty($field)) {
                $value = $this->valueCodesToIds($attribute, $value);
            }
        }

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
     * @param string   $operator
     * @param null|int $value
     * @param string   $field
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
        FieldFilterHelper::checkArray($field, $values, 'reference_data');

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, 'reference_data');
        }
    }

    /**
     * @param AttributeInterface $attribute
     * @param string             $value
     *
     * @return int
     */
    protected function valueCodesToIds(AttributeInterface $attribute, $value)
    {
        try {
            $value = $this->idsResolver->resolve($attribute->getReferenceDataName(), $value);
        } catch (\LogicException $e) {
            throw InvalidArgumentException::validEntityCodeExpected(
                $attribute->getCode(),
                'code',
                $e->getMessage(),
                'setter',
                'reference data',
                implode(',', $value)
            );
        }

        return $value;
    }
}
