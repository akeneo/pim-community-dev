<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;

/**
 * Media filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var array */
    protected $supportedAttributes;

    /**
     * Instanciate the base filter
     *
     * @param AttributeValidatorHelper $attrValidatorHelper
     * @param array                    $supportedAttributes
     * @param array                    $supportedOperators
     */
    public function __construct(
        AttributeValidatorHelper $attrValidatorHelper,
        array $supportedAttributes = [],
        array $supportedOperators = []
    ) {
        $this->attrValidatorHelper = $attrValidatorHelper;
        $this->supportedAttributes = $supportedAttributes;
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
        $this->checkLocaleAndScope($attribute, $locale, $scope, 'media');

        if ($operator !== Operators::IS_EMPTY) {
            $this->checkValue($attribute, $value);
            $this->addLikeFilter($attribute, $operator, $value, $locale, $scope);
        } else {
            $this->addIsEmptyFilter($attribute, $locale, $scope);
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
     * @param AttributeInterface $attribute the attribute
     * @param string             $locale    the locale
     * @param string             $scope     the scope
     */
    protected function addIsEmptyFilter(AttributeInterface $attribute, $locale, $scope)
    {
        // join on values
        $joinAlias = 'filter' . $attribute->getCode() . uniqid();
        $valueCondition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);
        $this->qb->leftJoin(
            $this->qb->getRootAlias() . '.values',
            $joinAlias,
            'WITH',
            $valueCondition
        );

        // join on media
        $joinAliasMedia = 'filterMedia' . $attribute->getCode() . uniqid();
        $backendType = $attribute->getBackendType();
        $backendField = sprintf('%s.%s', $joinAliasMedia, 'originalFilename');
        $this->qb->leftJoin($joinAlias . '.' . $backendType, $joinAliasMedia);
        $mediaCondition = $this->prepareCondition($backendField, Operators::IS_EMPTY, null);
        $this->qb->andWhere($mediaCondition);
    }

    /**
     * @param AttributeInterface $attribute the attribute
     * @param string             $operator  the used operator
     * @param string|array       $value     the value(s) to filter
     * @param string             $locale    the locale
     * @param string             $scope     the scope
     */
    protected function addLikeFilter(AttributeInterface $attribute, $operator, $value, $locale, $scope)
    {
        // join on values
        $joinAlias = 'filter' . $attribute->getCode() . uniqid();
        $valueCondition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);
        $this->qb->innerJoin(
            $this->qb->getRootAlias() . '.values',
            $joinAlias,
            'WITH',
            $valueCondition
        );

        // join on media
        $joinAliasMedia = 'filterMedia' . $attribute->getCode() . uniqid();
        $backendType = $attribute->getBackendType();
        $backendField = sprintf('%s.%s', $joinAliasMedia, 'originalFilename');
        $mediaCondition = $this->prepareCondition($backendField, $operator, $value);
        $this->qb->innerJoin($joinAlias . '.' . $backendType, $joinAliasMedia, 'WITH', $mediaCondition);
    }

    /**
     * Prepare conditions of the filter
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
            case Operators::EQUALS:
                $operator = 'LIKE';
                $value    = $value;
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
            throw InvalidArgumentException::stringExpected($attribute->getCode(), 'filter', 'media', gettype($value));
        }
    }
}
