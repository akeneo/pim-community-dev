<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderOptionsResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\SorterRegistryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
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

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** FilterRegistryInterface */
    protected $filterRegistry;

    /** SorterRegistryInterface */
    protected $sorterRegistry;

    /** CursorFactoryInterface */
    protected $cursorFactory;

    /** @var ProductQueryBuilderOptionsResolverInterface */
    protected $optionsResolver;

    /**
     * @param string                                      $pqbClass
     * @param AttributeRepositoryInterface                $attributeRepository
     * @param FilterRegistryInterface                     $filterRegistry
     * @param SorterRegistryInterface                     $sorterRegistry
     * @param CursorFactoryInterface                      $cursorFactory
     * @param ProductQueryBuilderOptionsResolverInterface $optionsResolver
     */
    public function __construct(
        string $pqbClass,
        AttributeRepositoryInterface $attributeRepository,
        FilterRegistryInterface $filterRegistry,
        SorterRegistryInterface $sorterRegistry,
        CursorFactoryInterface $cursorFactory,
        ProductQueryBuilderOptionsResolverInterface $optionsResolver
    ) {
        $this->pqbClass = $pqbClass;
        $this->attributeRepository = $attributeRepository;
        $this->filterRegistry = $filterRegistry;
        $this->sorterRegistry = $sorterRegistry;
        $this->cursorFactory = $cursorFactory;
        $this->optionsResolver = $optionsResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options = []): ProductQueryBuilderInterface
    {
        $options = $this->resolveOptions($options);

        $pqbOptions = [
            'locale' => $options['default_locale'],
            'scope'  => $options['default_scope'],
        ];

        if (isset($options['limit'])) {
            $pqbOptions['limit'] = $options['limit'];
        }

        if (isset($options['search_after'])) {
            $pqbOptions['search_after'] = $options['search_after'];
        }

        if (isset($options['search_after_unique_key'])) {
            $pqbOptions['search_after_unique_key'] = $options['search_after_unique_key'];
        }

        if (isset($options['from'])) {
            $pqbOptions['from'] = $options['from'];
        }

        $pqb = $this->createProductQueryBuilder($pqbOptions);
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
    protected function createProductQueryBuilder(array $options): ProductQueryBuilderInterface
    {
        $pqb = new $this->pqbClass(
            $this->attributeRepository,
            $this->filterRegistry,
            $this->sorterRegistry,
            $this->cursorFactory,
            $this->optionsResolver,
            $options
        );

        return $pqb;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function resolveOptions(array $options): array
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
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined([
            'repository_method',
            'repository_parameters',
            'currentGroup',
            'product',
            'default_locale',
            'default_scope',
            'filters',
            'search_after',
            'search_after_unique_key',
            'limit',
            'from'
        ]);
        $resolver->setDefaults([
            'repository_method'     => 'createQueryBuilder',
            'repository_parameters' => ['o'],
            'default_locale'        => null,
            'default_scope'         => null,
            'filters'               => [],
        ]);
        $resolver->setAllowedTypes('repository_method', 'string');
        $resolver->setAllowedTypes('repository_parameters', 'array');
        $resolver->setAllowedTypes('currentGroup', 'string');
        $resolver->setAllowedTypes('product', 'string');
        $resolver->setAllowedTypes('default_locale', ['string', 'null']);
        $resolver->setAllowedTypes('default_scope', ['string', 'null']);
        $resolver->setAllowedTypes('search_after', 'array');
        $resolver->setAllowedTypes('search_after_unique_key', ['string', 'null']);
        $resolver->setAllowedTypes('limit', 'int');
        $resolver->setAllowedTypes('filters', 'array');
        $resolver->setAllowedTypes('from', 'int');
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureFilterOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['field', 'operator', 'value'])
            ->setDefined(['context', 'type'])
            ->setDefaults([
                'context' => [],
                'type' => '',
            ])
            ->setAllowedTypes('context', 'array')
            ->setAllowedTypes('type', 'string')
        ;
    }
}
