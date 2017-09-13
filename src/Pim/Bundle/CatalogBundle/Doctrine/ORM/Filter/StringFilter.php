<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * String filter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StringFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var OptionsResolver */
    protected $resolver;

    /**
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param array                    $supportedAttributeTypes
     * @param array                    $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
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
            throw InvalidArgumentException::expectedFromPreviousException(
                $e,
                $attribute->getCode(),
                'filter',
                'string'
            );
        }

        $this->checkLocaleAndScope($attribute, $locale, $scope, 'string');

        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($options['field'], $value);
        }

        $joinAlias = $this->getUniqueAlias('filter' . $attribute->getCode());
        $backendField = sprintf('%s.%s', $joinAlias, $attribute->getBackendType());
        if (Operators::IS_EMPTY === $operator) {
            $this->qb->leftJoin(
                $this->qb->getRootAlias() . '.values',
                $joinAlias,
                'WITH',
                $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope)
            );
            $this->qb->andWhere($this->prepareCriteriaCondition($backendField, $operator, $value));
        } else {
            $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);
            if (Operators::IS_NOT_EMPTY === $operator) {
                $condition .= sprintf(
                    'AND (%s AND %s)',
                    $this->qb->expr()->isNotNull($backendField),
                    $this->qb->expr()->neq($backendField, $this->qb->expr()->literal(''))
                );
                $this->qb->innerJoin(
                    $this->qb->getRootAlias() . '.values',
                    $joinAlias,
                    'WITH',
                    $condition
                );
            } elseif (Operators::DOES_NOT_CONTAIN === $operator) {
                $whereCondition = $this->prepareCondition($backendField, $operator, $value) .
                    ' OR ' .
                    $this->prepareCondition($backendField, Operators::IS_NULL, null);

                $this->qb->leftJoin(
                    $this->qb->getRootAlias() . '.values',
                    $joinAlias,
                    'WITH',
                    $condition
                );
                $this->qb->andWhere($whereCondition);
            } else {
                $condition .= ' AND ' . $this->prepareCondition($backendField, $operator, $value);
                $this->qb->innerJoin(
                    $this->qb->getRootAlias() . '.values',
                    $joinAlias,
                    'WITH',
                    $condition
                );
            }
        }

        return $this;
    }

    /**
     * Prepare conditions of the filter
     *
     * @param string|array $backendField
     * @param string|array $operator
     * @param string|array $value
     *
     * @return string
     */
    protected function prepareCondition($backendField, $operator, $value)
    {
        if (null === $value) {
            $value = '';
        }

        $likeValue = str_replace(['%', '_'], ['\\%', '\\_'], $value);

        switch ($operator) {
            case Operators::STARTS_WITH:
                $operator = Operators::IS_LIKE;
                $value = $likeValue . '%';
                break;
            case Operators::ENDS_WITH:
                $operator = Operators::IS_LIKE;
                $value = '%' . $likeValue;
                break;
            case Operators::CONTAINS:
                $operator = Operators::IS_LIKE;
                $value = '%' . $likeValue . '%';
                break;
            case Operators::DOES_NOT_CONTAIN:
                $operator = Operators::NOT_LIKE;
                $value = '%' . $likeValue . '%';
                break;
            case Operators::EQUALS:
                $operator = Operators::IS_LIKE;
                break;
            case Operators::NOT_EQUAL:
                $operator = Operators::NOT_LIKE;
                break;
        }

        return $this->prepareCriteriaCondition($backendField, $operator, $value);
    }

    /**
     * Check if value is valid
     *
     * @param string $field
     * @param mixed  $value
     */
    protected function checkValue($field, $value)
    {
        if (is_array($value)) {
            foreach ($value as $scalarValue) {
                $this->checkScalarValue($field, $scalarValue);
            }
        } else {
            $this->checkScalarValue($field, $value);
        }
    }

    /**
     * @param string $field
     * @param mixed  $value
     */
    protected function checkScalarValue($field, $value)
    {
        if (!is_string($value) && null !== $value) {
            throw InvalidArgumentException::stringExpected($field, 'filter', 'string', gettype($value));
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
