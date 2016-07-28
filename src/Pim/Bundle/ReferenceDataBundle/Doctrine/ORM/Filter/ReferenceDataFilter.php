<?php

namespace Pim\Bundle\ReferenceDataBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\AbstractAttributeFilter;
use Pim\Bundle\ReferenceDataBundle\Doctrine\ReferenceDataIdResolver;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\Operators;
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
    /** @var ConfigurationRegistryInterface */
    protected $registry;

    /** @var ReferenceDataIdResolver */
    protected $idsResolver;

    /** @var OptionsResolver */
    protected $optionsResolver;

    /**
     * @param ConfigurationRegistryInterface $registry
     * @param ReferenceDataIdResolver        $idsResolver
     * @param array                          $supportedOperators
     */
    public function __construct(
        ConfigurationRegistryInterface $registry,
        ReferenceDataIdResolver $idsResolver,
        array $supportedOperators = []
    ) {
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
            throw InvalidArgumentException::expectedFromPreviousException(
                $e,
                $attribute->getCode(),
                'filter',
                'reference data simple select'
            );
        }

        if (Operators::IS_EMPTY !== $operator) {
            $field = $options['field'];
            $this->checkValue($field, $value);

            if (FieldFilterHelper::CODE_PROPERTY === FieldFilterHelper::getProperty($field)) {
                $value = $this->valueCodesToIds($attribute, $value);
            }

            $this->addNonEmptyFilter($attribute, $operator, $value, $locale, $scope);
        } else {
            $this->addEmptyFilter($attribute, $locale, $scope);
        }

        return $this;
    }

    /**
     * Add empty filter to the qb
     *
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $scope
     */
    protected function addEmptyFilter(
        AttributeInterface $attribute,
        $locale = null,
        $scope = null
    ) {
        $joinAlias = $this->getUniqueAlias('filter' . $attribute->getCode());

        // inner join to value
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);

        $this->qb->leftJoin(
            current($this->qb->getRootAliases()) . '.values',
            $joinAlias,
            'WITH',
            $condition
        );

        $referenceDataName = $attribute->getReferenceDataName();
        $joinAliasOpt = $this->getUniqueAlias('reference_data' . $referenceDataName);
        $backendField = sprintf('%s.%s', $joinAliasOpt, 'id');
        $condition    = $this->prepareCriteriaCondition($backendField, Operators::IS_EMPTY, null);
        $this->qb->leftJoin($joinAlias . '.' . $referenceDataName, $joinAliasOpt);
        $this->qb->andWhere($condition);
    }

    /**
     * Add non empty filter to the query
     *
     * @param AttributeInterface $attribute
     * @param string             $operator
     * @param string             $value
     * @param string             $locale
     * @param string             $scope
     */
    protected function addNonEmptyFilter(
        AttributeInterface $attribute,
        $operator,
        $value,
        $locale = null,
        $scope = null
    ) {
        $joinAlias = $this->getUniqueAlias('filter' . $attribute->getCode());

        // inner join to value
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);

        $this->qb->innerJoin(
            current($this->qb->getRootAliases()) . '.values',
            $joinAlias,
            'WITH',
            $condition
        );

        $referenceDataName = $attribute->getReferenceDataName();
        $joinAliasOpt = $this->getUniqueAlias('reference_data' . $referenceDataName);
        $backendField = sprintf('%s.%s', $joinAliasOpt, 'id');
        $condition    = $this->prepareCriteriaCondition($backendField, $operator, $value);
        $this->qb->innerJoin($joinAlias . '.' . $referenceDataName, $joinAliasOpt, 'WITH', $condition);
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
