<?php

namespace Pim\Component\Catalog\Query;

use Akeneo\Bundle\ElasticsearchBundle\Client as ElasticSearchClient;
use Akeneo\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Component\Catalog\Query\Filter\FilterRegistryInterface;
use Pim\Component\Catalog\Query\Sorter\SorterRegistryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Aims to wrap the creation and configuration of the product query builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductQueryBuilderFactory implements ProductQueryBuilderFactoryInterface
{
    /** @var string */
    protected $pqbClass;

    /** @var EntityManagerInterface */
    protected $om;

    /** @var string */
    protected $productClass;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** FilterRegistryInterface */
    protected $filterRegistry;

    /** SorterRegistryInterface */
    protected $sorterRegistry;

    /** CursorFactoryInterface */
    protected $cursorFactory;

    /** @var ElasticSearchClient */
    protected $searchEngine;

    /**
     * @param string                       $pqbClass
     * @param EntityManagerInterface       $om
     * @param string                       $productClass
     * @param AttributeRepositoryInterface $attributeRepository
     * @param FilterRegistryInterface      $filterRegistry
     * @param SorterRegistryInterface      $sorterRegistry
     * @param CursorFactoryInterface       $cursorFactory
     * @param ElasticSearchClient          $searchEngine
     */
    public function __construct(
        $pqbClass,
        EntityManagerInterface $om,
        $productClass,
        AttributeRepositoryInterface $attributeRepository,
        FilterRegistryInterface $filterRegistry,
        SorterRegistryInterface $sorterRegistry,
        CursorFactoryInterface $cursorFactory,
        ElasticSearchClient $searchEngine
    ) {
        $this->pqbClass = $pqbClass;
        $this->om = $om;
        $this->productClass = $productClass;
        $this->attributeRepository = $attributeRepository;
        $this->filterRegistry = $filterRegistry;
        $this->sorterRegistry = $sorterRegistry;
        $this->cursorFactory = $cursorFactory;
        $this->searchEngine = $searchEngine;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options = [])
    {
        $options = $this->resolveOptions($options);

        $pqb = $this->createProductQueryBuilder([
            'locale' => $options['default_locale'],
            'scope'  => $options['default_scope']
        ]);

        $pqb->setQueryBuilder(new SearchQueryBuilder());

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
            $this->attributeRepository,
            $this->filterRegistry,
            $this->sorterRegistry,
            $this->cursorFactory,
            $this->searchEngine,
            $this->om,
            $options
        );

        return $pqb;
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
            'currentGroup',
            'product',
            'default_locale',
            'default_scope',
            'filters',
        ]);
        $resolver->setDefaults([
            'repository_method'     => 'createQueryBuilder',
            'repository_parameters' => 'o',
            'default_locale'        => null,
            'default_scope'         => null,
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
