<?php

namespace Pim\Component\Catalog\Query;

use Akeneo\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Pim\Component\Catalog\Exception\UnsupportedFilterException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\FilterRegistryInterface;
use Pim\Component\Catalog\Query\Sorter\AttributeSorterInterface;
use Pim\Component\Catalog\Query\Sorter\FieldSorterInterface;
use Pim\Component\Catalog\Query\Sorter\SorterRegistryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Product query builder provides shortcuts to ease the appliance of filters and sorters on fields or attributes
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductQueryBuilder implements ProductQueryBuilderInterface
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var mixed */
    protected $qb;

    /** FilterRegistryInterface */
    protected $filterRegistry;

    /** SorterRegistryInterface */
    protected $sorterRegistry;

    /** @var array */
    protected $defaultContext;

    /** CursorFactoryInterface */
    protected $cursorFactory;

    /** @var array */
    protected $rawFilters = [];

    /**
     * Constructor
     *
     * @param AttributeRepositoryInterface $attributeRepository
     * @param FilterRegistryInterface      $filterRegistry
     * @param SorterRegistryInterface      $sorterRegistry
     * @param CursorFactoryInterface       $cursorFactory
     * @param array                        $defaultContext
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        FilterRegistryInterface $filterRegistry,
        SorterRegistryInterface $sorterRegistry,
        CursorFactoryInterface $cursorFactory,
        array $defaultContext
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->filterRegistry = $filterRegistry;
        $this->sorterRegistry = $sorterRegistry;
        $this->cursorFactory = $cursorFactory;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->defaultContext = $resolver->resolve($defaultContext);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        return $this->cursorFactory->createCursor($this->getQueryBuilder());
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
        if (!$this->qb) {
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
            $filter = $this->filterRegistry->getAttributeFilter($attribute, $operator);
        } else {
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
            'context'  => $context
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
        $sorter->addAttributeSorter($attribute, $direction, $context['locale'], $context['scope']);

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

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(
            [
                'locale',
                'scope'
            ]
        );
    }
}
