<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Query;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Builds a product query builder by using  shortcuts to easily select, filter or sort products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductQueryBuilder implements ProductQueryBuilderInterface
{
    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var mixed */
    protected $qb;

    /** QueryFilterRegistryInterface */
    protected $filterRegistry;

    /** QuerySorterRegistryInterface */
    protected $sorterRegistry;

    /** @var array */
    protected $defaultContext;

    /**
     * Constructor
     *
     * @param AttributeRepository          $attributeRepository
     * @param QueryFilterRegistryInterface $filterRegistry
     * @param QuerySorterRegistryInterface $sorterRegistry
     * @param array                        $defaultContext
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        QueryFilterRegistryInterface $filterRegistry,
        QuerySorterRegistryInterface $sorterRegistry,
        array $defaultContext
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->filterRegistry = $filterRegistry;
        $this->sorterRegistry = $sorterRegistry;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->defaultContext = $resolver->resolve($defaultContext);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        return $this->getQueryBuilder()->getQuery()->execute();
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
        $attribute = $this->attributeRepository->findOneByCode($field);

        if ($attribute !== null) {
            $filter = $this->filterRegistry->getAttributeFilter($attribute);
        } else {
            $filter = $this->filterRegistry->getFieldFilter($field);
        }

        if ($filter === null) {
            throw new \LogicException(
                sprintf('Filter on field "%s" is not supported', $field)
            );
        }

        if ($filter->supportsOperator($operator) === false) {
            throw new \LogicException(
                sprintf('Filter on field "%s" doesn\'t provide operator "%s"', $field, $operator)
            );
        }

        $context = $this->getFinalContext($context);
        if ($attribute !== null) {
            $this->addAttributeFilter($filter, $attribute, $operator, $value, $context);
        } else {
            $this->addFieldFilter($filter, $field, $operator, $value, $context);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addSorter($field, $direction, array $context = [])
    {
        $attribute = $this->attributeRepository->findOneByCode($field);

        if ($attribute !== null) {
            $sorter = $this->sorterRegistry->getAttributeSorter($attribute);
        } else {
            $sorter = $this->sorterRegistry->getFieldSorter($field);
        }

        if ($sorter === null) {
            throw new \LogicException(
                sprintf('Sorter on field "%s" is not supported', $field)
            );
        }

        $context = $this->getFinalContext($context);
        if ($attribute !== null) {
            $this->addAttributeSorter($sorter, $attribute, $direction, $context);
        } else {
            $this->addFieldSorter($sorter, $field, $direction, $context);
        }

        return $this;
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
        $context = $this->getFinalContext($context);
        $filter->setQueryBuilder($this->getQueryBuilder());
        $filter->addFieldFilter($field, $operator, $value, $context['locale'], $context['scope']);

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
        $context = $this->getFinalContext($context);
        $filter->setQueryBuilder($this->getQueryBuilder());
        $filter->addAttributeFilter($attribute, $operator, $value, $context['locale'], $context['scope']);

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
        $context = $this->getFinalContext($context);
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
        $context = $this->getFinalContext($context);
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
     * @param OptionsResolverInterface $resolver
     *
     * @return null
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(
            [
                'locale',
                'scope'
            ]
        );
    }
}
