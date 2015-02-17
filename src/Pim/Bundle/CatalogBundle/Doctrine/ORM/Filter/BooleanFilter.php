<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterHelper;
use Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;

/**
 * Boolean filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanFilter extends AbstractAttributeFilter implements AttributeFilterInterface, FieldFilterInterface
{
    /** @var array */
    protected $supportedAttributes;

    /** @var array */
    protected $supportedFields;

    /**
     * Instanciate the base filter
     *
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param array                    $supportedAttributes
     * @param array                    $supportedFields
     * @param array                    $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        array $supportedAttributes = [],
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
        $this->supportedAttributes = $supportedAttributes;
        $this->supportedFields     = $supportedFields;
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
        $this->checkLocaleAndScope($attribute, $locale, $scope, 'boolean');

        if (!is_bool($value)) {
            throw InvalidArgumentException::booleanExpected(
                $attribute->getCode(),
                'filter',
                'boolean',
                gettype($value)
            );
        }

        $joinAlias    = $this->getUniqueAlias('filter' . $attribute->getCode());
        $backendField = sprintf('%s.%s', $joinAlias, $attribute->getBackendType());

        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);
        $condition .= ' AND ' . $this->prepareCriteriaCondition($backendField, $operator, $value);
        $this->qb->innerJoin(
            $this->qb->getRootAlias() . '.values',
            $joinAlias,
            'WITH',
            $condition
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $scope = null, $options = [])
    {
        if (!is_bool($value)) {
            throw InvalidArgumentException::booleanExpected($field, 'filter', 'boolean', gettype($value));
        }

        $field = current($this->qb->getRootAliases()) . '.' . FieldFilterHelper::getCode($field);
        $condition = $this->prepareCriteriaCondition($field, $operator, $value);
        $this->qb->andWhere($condition);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsField($field)
    {
        return in_array($field, $this->supportedFields);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array($attribute->getAttributeType(), $this->supportedAttributes);
    }
}
