<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Doctrine\Query\Operators;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * String filter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StringFilter extends AbstractFilter implements AttributeFilterInterface
{
    /** @var array */
    protected $supportedAttributes;

    /** @var OptionsResolver */
    protected $resolver;

    /**
     * Instanciate the base filter
     *
     * @param array $supportedAttributes
     * @param array $supportedOperators
     */
    public function __construct(
        array $supportedAttributes = [],
        array $supportedOperators = []
    ) {
        $this->supportedAttributes = $supportedAttributes;
        $this->supportedOperators  = $supportedOperators;

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
        $options = $this->resolver->resolve($options);
        if ($operator !== Operators::IS_EMPTY) {
            $this->checkValue($options['field'], $value);
        }

        $joinAlias = 'filter'.$attribute->getCode();
        $backendField = sprintf('%s.%s', $joinAlias, $attribute->getBackendType());

        if ($operator === Operators::IS_EMPTY) {
            $this->qb->leftJoin(
                $this->qb->getRootAlias().'.values',
                $joinAlias,
                'WITH',
                $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope)
            );
            $this->qb->andWhere($this->prepareCriteriaCondition($backendField, $operator, $value));
        } else {
            $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);
            $condition .= ' AND ' . $this->prepareCondition($backendField, $operator, $value);

            $this->qb->innerJoin(
                $this->qb->getRootAlias() . '.values',
                $joinAlias,
                'WITH',
                $condition
            );
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array($attribute->getAttributeType(), $this->supportedAttributes);
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
        switch ($operator) {
            case Operators::STARTS_WITH:
                $operator = 'LIKE';
                $value    = $value . '%';
                break;
            case Operators::ENDS_WITH:
                $operator = 'LIKE';
                $value    = '%' . $value;
                break;
            case Operators::CONTAINS:
                $operator = 'LIKE';
                $value    = '%' . $value . '%';
                break;
            case Operators::DOES_NOT_CONTAIN:
                $operator = 'NOT LIKE';
                $value    = '%' . $value . '%';
                break;
            default:
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
        if (!is_string($value) && !is_array($value)) {
            throw InvalidArgumentException::stringExpected($field, 'filter', 'string');
        }

        if (is_array($value)) {
            foreach ($value as $stringValue) {
                if (!is_string($stringValue)) {
                    throw InvalidArgumentException::stringExpected($field, 'filter', 'string');
                }
            }
        }
    }

    /**
     * Configure the option resolver
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['field']);
        $resolver->setOptional(['locale', 'scope']);
    }
}
