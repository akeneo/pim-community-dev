<?php

namespace Pim\Component\Catalog\Query;

use Pim\Component\Catalog\Query\Filter\Operators;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Aims to wrap the creation and configuration of the product query builder with filters.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilteredProductQueryBuilderFactory implements ProductQueryBuilderFactoryInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $factory;

    /**
     * @param ProductQueryBuilderFactoryInterface $factory
     */
    public function __construct(ProductQueryBuilderFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options = [])
    {
        $filters = [];
        if (array_key_exists('filters', $options)) {
            $filters = $options['filters'];
            unset($options['filters']);
        }

        $productQueryBuilder = $this->factory->create($options);

        if (0 === count($filters)) {
            return $productQueryBuilder;
        }

        $resolver = new OptionsResolver();
        $resolver
            ->setRequired(['field', 'operator', 'value'])
            ->setDefined(['context'])
            ->setDefaults([
                'context'  => [],
                'operator' => Operators::EQUALS
            ]);

        foreach ($filters as $filter) {
            $filter = $resolver->resolve($filter);
            $productQueryBuilder->addFilter(
                $filter['field'],
                $filter['operator'],
                $filter['value'],
                $filter['context']
            );
        }

        return $productQueryBuilder;
    }
}
