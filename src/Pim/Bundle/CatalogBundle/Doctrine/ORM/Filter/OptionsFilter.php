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
 * Filtering by multi option backend type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsFilter extends AbstractAttributeFilter implements AttributeFilterInterface
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

        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($options['field'], $value);
        }

        $joinAlias = $this->getUniqueAlias('filter' . $attribute->getCode());
        $joinAliasOpt = $this->getUniqueAlias('filterO' . $attribute->getCode());
        $backendField = sprintf('%s.%s', $joinAliasOpt, 'id');

        if (Operators::IS_EMPTY === $operator || Operators::IS_NOT_EMPTY === $operator) {
            $this->qb->leftJoin(
                $this->qb->getRootAlias() . '.values',
                $joinAlias,
                'WITH',
                $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope)
            );

            $this->qb
                ->leftJoin($joinAlias . '.' . $attribute->getBackendType(), $joinAliasOpt)
                ->andWhere($this->prepareCriteriaCondition($backendField, $operator, null));
        } else {
            if (FieldFilterHelper::getProperty($options['field']) === FieldFilterHelper::CODE_PROPERTY) {
                $value = $this->objectIdResolver->getIdsFromCodes('option', $value, $attribute);
            }
            $this->qb
                ->innerJoin(
                    $this->qb->getRootAlias() . '.values',
                    $joinAlias,
                    'WITH',
                    $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope)
                )
                ->innerJoin(
                    $joinAlias . '.' . $attribute->getBackendType(),
                    $joinAliasOpt,
                    'WITH',
                    $this->prepareCriteriaCondition($backendField, $operator, $value)
                );

            if (Operators::NOT_IN_LIST === $operator) {
                $this->qb->andWhere($this->qb->expr()->notIn(
                    $this->qb->getRootAlias() . '.id',
                    $this->getNotInSubquery($attribute, $locale, $scope, $value)
                ));
            }
        }

        return $this;
    }

    /**
     * Subrequest matching all products that actually have $value options as product values.
     *
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $scope
     * @param array              $value
     *
     * @return string
     */
    protected function getNotInSubquery(AttributeInterface $attribute, $locale, $scope, $value)
    {
        $notInQb = $this->qb->getEntityManager()->createQueryBuilder();
        $rootEntity = current($this->qb->getRootEntities());
        $notInAlias = $this->getUniqueAlias('productsNotIn');
        $joinAlias = $this->getUniqueAlias('filter' . $attribute->getCode());
        $joinAliasOpt = $this->getUniqueAlias('filterO' . $attribute->getCode());

        $notInQb->select($notInAlias . '.id')
            ->from($rootEntity, $notInAlias, $notInAlias . '.id')
            ->innerJoin(
                $notInQb->getRootAlias() . '.values',
                $joinAlias,
                'WITH',
                $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope)
            )
            ->innerJoin(
                $joinAlias . '.' . $attribute->getBackendType(),
                $joinAliasOpt
            )
            ->where($notInQb->expr()->in($joinAliasOpt . '.id', $value));

        return $notInQb->getDQL();
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
