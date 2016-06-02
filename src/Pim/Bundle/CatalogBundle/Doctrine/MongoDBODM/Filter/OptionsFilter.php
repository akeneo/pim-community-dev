<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface;
use Pim\Bundle\CatalogBundle\ProductQueryUtility;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Multi options filter for MongoDB
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var array */
    protected $supportedAttributes;

    /** @var ObjectIdResolverInterface */
    protected $objectIdResolver;

    /** @var OptionsResolver */
    protected $resolver;

    /**
     * @param AttributeValidatorHelper  $attrValidatorHelper
     * @param ObjectIdResolverInterface $objectIdResolver
     * @param array                     $supportedAttributes
     * @param array                     $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        ObjectIdResolverInterface $objectIdResolver,
        array $supportedAttributes = [],
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
        $this->objectIdResolver    = $objectIdResolver;
        $this->supportedAttributes = $supportedAttributes;
        $this->supportedOperators  = $supportedOperators;

        $this->resolver = new OptionsResolver();
        $this->configureOptions($this->resolver);
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
        try {
            $options = $this->resolver->resolve($options);
        } catch (\Exception $e) {
            throw InvalidArgumentException::expectedFromPreviousException(
                $e,
                $attribute->getCode(),
                'filter',
                'options'
            );
        }

        $this->checkLocaleAndScope($attribute, $locale, $scope, 'options');

        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($options['field'], $value);

            if (FieldFilterHelper::CODE_PROPERTY === FieldFilterHelper::getProperty($options['field'])) {
                $value = $this->objectIdResolver->getIdsFromCodes('option', $value, $attribute);
            } else {
                $value = array_map('intval', $value);
            }
        }

        $field = sprintf(
            '%s.%s.id',
            ProductQueryUtility::NORMALIZED_FIELD,
            ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $locale, $scope)
        );

        $this->applyFilter($field, $operator, $value);

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
        FieldFilterHelper::checkArray($field, $values, 'options');

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, 'options');
        }
    }

    /**
     * Apply the filter to the query with the given operator
     *
     * @param string $field
     * @param string $operator
     * @param mixed  $value
     */
    protected function applyFilter($field, $operator, $value)
    {
        switch ($operator) {
            case Operators::IN_LIST:
                $this->qb->field($field)->in($value);
                break;
            case Operators::NOT_IN_LIST:
                $this->qb->field($field)->exists(true);
                $this->qb->field($field)->notIn($value);
                break;
            case Operators::IS_EMPTY:
                $this->qb->field($field)->exists(false);
                break;
            case Operators::IS_NOT_EMPTY:
                $this->qb->field($field)->exists(true);
                break;
        }
    }

    /**
     * Configure the option resolver
     *
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['field']);
        $resolver->setDefined(['locale', 'scope']);
    }
}
