<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Media filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
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
        $this->checkLocaleAndScope($attribute, $locale, $scope);

        if ($operator === Operators::IS_EMPTY || $operator === Operators::IS_NOT_EMPTY) {
            $this->addEmptyTypeFilter($attribute, $operator, $locale, $scope);
        } else {
            $this->checkValue($attribute, $value);
            $this->addFilter($attribute, $operator, $value, $locale, $scope);
        }

        return $this;
    }

    /**
     * @param AttributeInterface $attribute the attribute
     * @param string             $operator  the used operator
     * @param string|array       $value     the value(s) to filter
     * @param string             $locale    the locale
     * @param string             $scope     the scope
     */
    protected function addFilter(AttributeInterface $attribute, $operator, $value, $locale, $scope)
    {
        $joinAlias = $this->getUniqueAlias('filter' . $attribute->getCode());
        $joinAliasMedia = $this->getUniqueAlias('filterMedia' . $attribute->getCode());
        $backendField = sprintf('%s.%s', $joinAliasMedia, 'originalFilename');
        $backendType = $attribute->getBackendType();

        $this->addValuesInnerJoin($attribute, $locale, $scope, $joinAlias);
        $this->qb->innerJoin(
            $joinAlias . '.' . $backendType,
            $joinAliasMedia,
            'WITH',
            $this->prepareCondition($backendField, $operator, $value)
        );
    }

    /**
     * @param AttributeInterface $attribute the attribute
     * @param string             $operator  the operator
     * @param string             $locale    the locale
     * @param string             $scope     the scope
     */
    protected function addEmptyTypeFilter(AttributeInterface $attribute, $operator, $locale, $scope)
    {
        $joinAlias = $this->getUniqueAlias('filter' . $attribute->getCode());
        $joinAliasMedia = $this->getUniqueAlias('filterMedia' . $attribute->getCode());
        $backendField = sprintf('%s.%s', $joinAliasMedia, 'originalFilename');

        $this->addValuesLeftJoin($attribute, $locale, $scope, $joinAlias);
        $this->addMediaLeftJoin($attribute, $joinAlias, $joinAliasMedia);
        $this->qb->andWhere($this->prepareCondition($backendField, $operator, null));
    }

    /**
     * @param AttributeInterface $attribute the attribute
     * @param string             $locale    the locale
     * @param string             $scope     the scope
     * @param string             $joinAlias the join alias
     */
    protected function addValuesLeftJoin(AttributeInterface $attribute, $locale, $scope, $joinAlias)
    {
        $valueCondition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);
        $this->qb->leftJoin(
            current($this->qb->getRootAliases()) . '.values',
            $joinAlias,
            'WITH',
            $valueCondition
        );
    }

    /**
     * @param AttributeInterface $attribute      the attribute
     * @param string             $joinAlias      the join alias
     * @param string             $joinAliasMedia the join alias
     */
    protected function addMediaLeftJoin(AttributeInterface $attribute, $joinAlias, $joinAliasMedia)
    {
        $backendType = $attribute->getBackendType();
        $this->qb->leftJoin($joinAlias . '.' . $backendType, $joinAliasMedia);
    }

    /**
     * @param AttributeInterface $attribute the attribute
     * @param string             $locale    the locale
     * @param string             $scope     the scope
     * @param string             $joinAlias the join alias
     */
    protected function addValuesInnerJoin(AttributeInterface $attribute, $locale, $scope, $joinAlias)
    {
        $valueCondition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);
        $this->qb->innerJoin(
            current($this->qb->getRootAliases()) . '.values',
            $joinAlias,
            'WITH',
            $valueCondition
        );
    }

    /**
     * Prepare conditions of the filter
     *
     * @param string       $backendField
     * @param string|array $operator
     * @param string|array $value
     *
     * @return string
     */
    protected function prepareCondition($backendField, $operator, $value)
    {
        switch ($operator) {
            case Operators::STARTS_WITH:
                $operator = Operators::IS_LIKE;
                $value = $value . '%';
                break;
            case Operators::ENDS_WITH:
                $operator = Operators::IS_LIKE;
                $value = '%' . $value;
                break;
            case Operators::CONTAINS:
                $operator = Operators::IS_LIKE;
                $value = '%' . $value . '%';
                break;
            case Operators::DOES_NOT_CONTAIN:
                $operator = Operators::IS_NOT_LIKE;
                $value = '%' . $value . '%';
                break;
            case Operators::EQUALS:
                $operator = Operators::IS_LIKE;
                break;
            case Operators::NOT_EQUAL:
                $operator = Operators::IS_NOT_LIKE;
                break;
            default:
                break;
        }

        return $this->prepareCriteriaCondition($backendField, $operator, $value);
    }

    /**
     * @param AttributeInterface $attribute
     * @param mixed              $value
     */
    protected function checkValue(AttributeInterface $attribute, $value)
    {
        if (!is_string($value)) {
            throw InvalidPropertyTypeException::stringExpected($attribute->getCode(), static::class, $value);
        }
    }
}
