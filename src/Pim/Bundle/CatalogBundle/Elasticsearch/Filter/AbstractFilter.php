<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\FilterInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

abstract class AbstractFilter implements FilterInterface
{
    /** @var SearchQueryBuilder */
    protected $searchQueryBuilder = null;

    /** @var array */
    protected $supportedOperators = [];

    /** @var AttributeValidatorHelper */
    protected $attrValidatorHelper;

    /** @var string[] */
    protected $supportedAttributeTypes;

    /** @var string[] */
    protected $supportedFields;

    /**
     * {@inheritdoc}
     */
    public function supportsOperator($operator)
    {
        return in_array($operator, $this->supportedOperators);
    }

    /**
     * {@inheritdoc}
     */
    public function getOperators()
    {
        return $this->supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeTypes()
    {
        return $this->supportedAttributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array($attribute->getAttributeType(), $this->supportedAttributeTypes);
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
    public function getFields()
    {
        return $this->supportedFields;
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryBuilder($searchQueryBuilder)
    {
        if (!$searchQueryBuilder instanceof SearchQueryBuilder) {
            throw new \InvalidArgumentException(
                sprintf('Query builder should be an instance of "%s"', SearchQueryBuilder::class)
            );
        }

        $this->searchQueryBuilder = $searchQueryBuilder;
    }

    /**
     * Check locale and scope are valid
     *
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $scope
     *
     * @throws InvalidPropertyException
     */
    protected function checkLocaleAndScope(AttributeInterface $attribute, $locale, $scope)
    {
        try {
            $this->attrValidatorHelper->validateLocale($attribute, $locale);
            $this->attrValidatorHelper->validateScope($attribute, $scope);
        } catch (\LogicException $e) {
            throw InvalidPropertyException::expectedFromPreviousException(
                $attribute->getCode(),
                static::class,
                $e
            );
        }
    }

    /**
     * Calculates the ES path to a product value indexed in ES.
     *
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $scope
     *
     * @return string
     */
    protected function getAttributePath(AttributeInterface $attribute, $locale, $scope)
    {
        $locale = (null === $locale) ? '<all_locales>' : $locale;
        $scope = (null === $scope) ? '<all_channels>' : $scope;

        return 'values.' . $attribute->getCode() . '-' . $attribute->getBackendType() . '.' . $locale . '.' . $scope;
    }
}
