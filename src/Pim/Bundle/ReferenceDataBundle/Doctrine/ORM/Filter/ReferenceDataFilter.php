<?php

namespace Pim\Bundle\ReferenceDataBundle\Doctrine\ORM\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\AbstractAttributeFilter;
use Pim\Bundle\ReferenceDataBundle\Doctrine\ReferenceDataIdResolver;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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

    /** @var OptionsResolver */
    protected $optionsResolver;

    /**
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
        $this->supportedOperators = $supportedOperators;

        $this->optionsResolver = new OptionsResolver();
        $this->optionsResolver->setRequired(['field'])
            ->setDefined(['locale', 'scope']);
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
            $options = $this->optionsResolver->resolve($options);
        } catch (\Exception $e) {
            throw InvalidPropertyException::expectedFromPreviousException(
                $attribute->getCode(),
                static::class,
                $e
            );
        }

        $this->checkLocaleAndScope($attribute, $locale, $scope);
        $joinAlias = $this->getUniqueAlias('filter' . $attribute->getCode());
        $joinCondition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);

        if (!in_array($operator, [Operators::IS_EMPTY, Operators::IS_NOT_EMPTY])) {
            $field = $options['field'];
            $this->checkValue($field, $value);

            if (FieldFilterHelper::CODE_PROPERTY === FieldFilterHelper::getProperty($field)) {
                $value = $this->valueCodesToIds($attribute, $value);
            }

            $join = 'innerJoin';
        } else {
            $join = 'leftJoin';
            $value = null;
        }

        $this->qb->$join(
            current($this->qb->getRootAliases()) . '.values',
            $joinAlias,
            'WITH',
            $joinCondition
        );

        $referenceDataName = $attribute->getReferenceDataName();
        $joinAliasOpt = $this->getUniqueAlias('reference_data' . $referenceDataName);
        $backendField = sprintf('%s.%s', $joinAliasOpt, 'id');

        $whereCondition = $this->prepareCriteriaCondition($backendField, $operator, $value);

        if (!in_array($operator, [Operators::IS_EMPTY, Operators::IS_NOT_EMPTY])) {
            $this->qb->$join($joinAlias . '.' . $referenceDataName, $joinAliasOpt, 'WITH', $whereCondition);
        } else {
            $this->qb->$join($joinAlias . '.' . $referenceDataName, $joinAliasOpt);
            $this->qb->andWhere($whereCondition);
        }

        return $this;
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
        FieldFilterHelper::checkArray($field, $values, static::class);

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($field, $value, static::class);
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
            throw InvalidPropertyException::validEntityCodeExpected(
                $attribute->getCode(),
                'code',
                $e->getMessage(),
                static::class,
                implode(',', $value)
            );
        }

        return $value;
    }
}
