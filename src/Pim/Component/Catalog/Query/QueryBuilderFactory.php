<?php

namespace Pim\Component\Catalog\Query;

use Akeneo\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Component\Catalog\Query\Filter\FilterRegistryInterface;
use Pim\Component\Catalog\Query\Sorter\SorterRegistryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Aims to wrap the creation and configuration of the product query builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QueryBuilderFactory implements ProductQueryBuilderFactoryInterface
{
    /** @var string */
    protected $pqbClass;

    /** @var ObjectManager */
    protected $om;

    /** @var string */
    protected $productClass;

    /** FilterRegistryInterface */
    protected $filterRegistry;

    /** SorterRegistryInterface */
    protected $sorterRegistry;

    /** CursorFactoryInterface */
    protected $cursorFactory;

    /**
     * @param string                       $pqbClass
     * @param ObjectManager                $om
     * @param string                       $productClass
     * @param FilterRegistryInterface      $filterRegistry
     * @param SorterRegistryInterface      $sorterRegistry
     * @param CursorFactoryInterface       $cursorFactory
     */
    public function __construct(
        $pqbClass,
        ObjectManager $om,
        $productClass,
        FilterRegistryInterface $filterRegistry,
        SorterRegistryInterface $sorterRegistry,
        CursorFactoryInterface $cursorFactory
    ) {
        $this->pqbClass = $pqbClass;
        $this->om = $om;
        $this->productClass = $productClass;
        $this->filterRegistry = $filterRegistry;
        $this->sorterRegistry = $sorterRegistry;
        $this->cursorFactory = $cursorFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options = [])
    {
        $options = $this->resolveOptions($options);

        $pqb = $this->createProductQueryBuilder($options);

        $qb = $this->createQueryBuilder($options);
        $pqb->setQueryBuilder($qb);

        foreach ($options['filters'] as $filter) {
            $pqb->addFilter($filter['field'], $filter['operator'], $filter['value'], $filter['context']);
        }

        return $pqb;
    }

    /**
     * @param array $options
     *
     * @return ProductQueryBuilderInterface
     */
    protected function createProductQueryBuilder(array $options)
    {
        $pqb = new $this->pqbClass(
            $this->filterRegistry,
            $this->sorterRegistry,
            $this->cursorFactory,
            $options
        );

        return $pqb;
    }

    /**
     * @param array $options
     *
     * @return \Doctrine\ORM\QueryBuilder|\Doctrine\ODM\MongoDB\Query\Builder
     */
    protected function createQueryBuilder(array $options)
    {
        $repository = $this->om->getRepository($this->productClass);
        $method = $options['repository_method'];
        $parameters = $options['repository_parameters'];
        $qb = $repository->$method($parameters);

        return $qb;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function resolveOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $options = $resolver->resolve($options);

        $filterResolver = new OptionsResolver();
        $this->configureFilterOptions($filterResolver);

        $filters = $options['filters'];
        $options['filters'] = [];
        foreach ($filters as $filter) {
            $options['filters'][] = $filterResolver->resolve($filter);
        }

        return $options;
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined([
            'repository_method',
            'repository_parameters',
            'filters',
        ]);
        $resolver->setDefaults([
            'repository_method'     => 'createQueryBuilder',
            'repository_parameters' => 'o',
            'filters'               => [],
        ]);
        $resolver->setAllowedTypes('filters', 'array');
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureFilterOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['field', 'operator', 'value'])
            ->setDefined(['context'])
            ->setDefaults([
                'context'  => [],
            ]);
    }
}
