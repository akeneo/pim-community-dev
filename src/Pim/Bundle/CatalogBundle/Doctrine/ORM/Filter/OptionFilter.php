<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Filtering by simple option backend type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var ObjectIdResolverInterface */
    protected $objectIdResolver;

    /** @var OptionsResolver */
    protected $resolver;

    /**
     * @param AttributeValidatorHelper  $attrValidatorHelper
     * @param ObjectIdResolverInterface $objectIdResolver
     * @param string[]                  $supportedAttributeTypes
     * @param string[]                  $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        ObjectIdResolverInterface $objectIdResolver,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
        $this->objectIdResolver = $objectIdResolver;
        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->supportedOperators = $supportedOperators;

        $this->resolver = new OptionsResolver();
        $this->configureOptions($this->resolver);
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
            throw InvalidPropertyException::expectedFromPreviousException(
                $attribute->getCode(),
                static::class,
                $e
            );
        }

        $this->checkLocaleAndScope($attribute, $locale, $scope);
        $field = $options['field'];

        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($field, $value);
        }

        $joinAlias = $this->getUniqueAlias('filter' . $attribute->getCode());

        // prepare join value condition
        $optionAlias = $joinAlias . '.option';

        if (Operators::IS_EMPTY === $operator || Operators::IS_NOT_EMPTY === $operator) {
            $this->qb->leftJoin(
                $this->qb->getRootAlias() . '.values',
                $joinAlias,
                'WITH',
                $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope)
            );

            $this->qb->andWhere($this->prepareCriteriaCondition($optionAlias, $operator, null));
        } else {
            // inner join to value
            $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);

            if (FieldFilterHelper::getProperty($field) === FieldFilterHelper::CODE_PROPERTY) {
                $value = $this->objectIdResolver->getIdsFromCodes('option', $value, $attribute);
            }

            $condition .= ' AND ' . $this->prepareCriteriaCondition($optionAlias, $operator, $value);

            $this->qb->innerJoin(
                $this->qb->getRootAlias().'.values',
                $joinAlias,
                'WITH',
                $condition
            );
        }

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
        FieldFilterHelper::checkArray($field, $values, static::class);

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, static::class);
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
