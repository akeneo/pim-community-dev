<?php


namespace Akeneo\Pim\Enrichment\Component\Product\Query;

use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\AttributeSorterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\FieldSorterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\SorterRegistryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;

class AbstractEntityWithValuesQueryBuilder implements ProductQueryBuilderInterface
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var mixed */
    protected $qb;

    /** @var FilterRegistryInterface */
    protected $filterRegistry;

    /** @var SorterRegistryInterface */
    protected $sorterRegistry;

    /** @var array */
    protected $defaultContext;

    /** CursorFactoryInterface */
    protected $cursorFactory;

    /** @var ProductQueryBuilderOptionsResolverInterface */
    protected $optionResolver;

    /** @var array */
    protected $rawFilters = [];

    /**
     * @param AttributeRepositoryInterface                $attributeRepository
     * @param FilterRegistryInterface                     $filterRegistry
     * @param SorterRegistryInterface                     $sorterRegistry
     * @param CursorFactoryInterface                      $cursorFactory
     * @param ProductQueryBuilderOptionsResolverInterface $optionResolver
     * @param array                                       $defaultContext
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        FilterRegistryInterface $filterRegistry,
        SorterRegistryInterface $sorterRegistry,
        CursorFactoryInterface $cursorFactory,
        ProductQueryBuilderOptionsResolverInterface $optionResolver,
        array $defaultContext
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->filterRegistry = $filterRegistry;
        $this->sorterRegistry = $sorterRegistry;
        $this->cursorFactory = $cursorFactory;
        $this->optionResolver = $optionResolver;

        $this->defaultContext = $this->optionResolver->resolve($defaultContext);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $allowedCursorOptions = ['page_size', 'search_after', 'search_after_unique_key', 'limit', 'from'];
        $cursorOptions = array_filter(
            $this->defaultContext,
            function ($key) use ($allowedCursorOptions) {
                return in_array($key, $allowedCursorOptions);
            },
            ARRAY_FILTER_USE_KEY
        );

        return $this->cursorFactory->createCursor($this->getQueryBuilder()->getQuery(), $cursorOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryBuilder($queryBuilder)
    {
        $this->qb = $queryBuilder;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryBuilder()
    {
        if (null === $this->qb) {
            throw new \LogicException('Query builder must be configured');
        }

        return $this->qb;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter($field, $operator, $value, array $context = [])
    {
        $code = FieldFilterHelper::getCode($field);
        $attribute = $this->attributeRepository->findOneByIdentifier($code);

        // In case of non case sensitive database configuration you can have attributes with code matching a field.
        // For example "id" would match an attribute named "ID" so we double check here that we are adding the desired
        // filter (ref PIM-6064)
        if (null !== $attribute && $attribute->getCode() !== $code) {
            $attribute = null;
        }

        if (null !== $attribute) {
            $filterType = 'attribute';
            $filter = $this->filterRegistry->getAttributeFilter($attribute, $operator);
        } else {
            $filterType = 'field';
            $filter = $this->filterRegistry->getFieldFilter($field, $operator);
        }

        if (null === $filter) {
            throw new UnsupportedFilterException(
                sprintf('Filter on property "%s" is not supported or does not support operator "%s"', $field, $operator)
            );
        }

        $context = $this->getFinalContext($context);
        if (null !== $attribute) {
            $context['field'] = $field;

            $this->addAttributeFilter($filter, $attribute, $operator, $value, $context);
        } else {
            $this->addFieldFilter($filter, $field, $operator, $value, $context);
        }

        $this->rawFilters[] = [
            'field'    => $field,
            'operator' => $operator,
            'value'    => $value,
            'context'  => $context,
            'type'     => $filterType
        ];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addSorter($field, $direction, array $context = [])
    {
        $attribute = $this->attributeRepository->findOneBy(['code' => $field]);

        if (null !== $attribute) {
            $sorter = $this->sorterRegistry->getAttributeSorter($attribute);
        } else {
            $sorter = $this->sorterRegistry->getFieldSorter($field);
        }

        if (null === $sorter) {
            throw new \LogicException(
                sprintf('Sorter on field "%s" is not supported', $field)
            );
        }

        $context = $this->getFinalContext($context);
        if (null !== $attribute) {
            $this->addAttributeSorter($sorter, $attribute, $direction, $context);
        } else {
            $this->addFieldSorter($sorter, $field, $direction, $context);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawFilters()
    {
        return $this->rawFilters;
    }

    /**
     * Add a filter condition on a field
     *
     * @param FieldFilterInterface $filter   the filter
     * @param string               $field    the field
     * @param string               $operator the operator
     * @param mixed                $value    the value to filter
     * @param array                $context  the filter context
     *
     * @return ProductQueryBuilderInterface
     */
    protected function addFieldFilter(FieldFilterInterface $filter, $field, $operator, $value, array $context)
    {
        $filter->setQueryBuilder($this->getQueryBuilder());
        $filter->addFieldFilter($field, $operator, $value, $context['locale'], $context['scope'], $context);

        return $this;
    }

    /**
     * Add a filter condition on an attribute
     *
     * @param AttributeFilterInterface $filter    the filter
     * @param AttributeInterface       $attribute the attribute
     * @param string                   $operator  the operator
     * @param mixed                    $value     the value to filter
     * @param array                    $context   the filter context
     *
     * @return ProductQueryBuilderInterface
     */
    protected function addAttributeFilter(
        AttributeFilterInterface $filter,
        AttributeInterface $attribute,
        $operator,
        $value,
        array $context
    ) {
        $locale = $attribute->isLocalizable() ? $context['locale'] : null;
        $scope = $attribute->isScopable() ? $context['scope'] : null;

        $filter->setQueryBuilder($this->getQueryBuilder());
        $filter->addAttributeFilter($attribute, $operator, $value, $locale, $scope, $context);

        // The products without family should not be returned when filtering on an empty value,
        // as empty optional values are considered inexistant
        if (Operators::IS_EMPTY === $operator
            || Operators::IS_EMPTY_FOR_CURRENCY === $operator
            || Operators::IS_EMPTY_ON_ALL_CURRENCIES === $operator) {
            $this->addFilter('family', Operators::IS_NOT_EMPTY, null);
        }

        return $this;
    }

    /**
     * Sort by field
     *
     * @param FieldSorterInterface $sorter    the sorter
     * @param string               $field     the field to sort on
     * @param string               $direction the direction to use
     * @param array                $context   the sorter context
     *
     * @return ProductQueryBuilderInterface
     */
    protected function addFieldSorter(FieldSorterInterface $sorter, $field, $direction, array $context)
    {
        $sorter->setQueryBuilder($this->getQueryBuilder());
        $sorter->addFieldSorter($field, $direction, $context['locale'], $context['scope']);

        return $this;
    }

    /**
     * Sort by attribute value
     *
     * @param AttributeSorterInterface $sorter    the sorter
     * @param AttributeInterface       $attribute the attribute to sort on
     * @param string                   $direction the direction to use
     * @param array                    $context   the sorter context
     *
     * @return ProductQueryBuilderInterface
     */
    protected function addAttributeSorter(
        AttributeSorterInterface $sorter,
        AttributeInterface $attribute,
        $direction,
        array $context
    ) {
        $sorter->setQueryBuilder($this->getQueryBuilder());

        $localeCode = !$attribute->isLocalizable() && !$attribute->isLocaleSpecific() ? null : $context['locale'];
        $scopeCode = !$attribute->isScopable() ? null : $context['scope'];

        $sorter->addAttributeSorter($attribute, $direction, $localeCode, $scopeCode);

        return $this;
    }

    /**
     * Merge default context with provided one
     *
     * @param array $context
     *
     * @return array
     */
    protected function getFinalContext(array $context)
    {
        return array_merge($this->defaultContext, $context);
    }
}
