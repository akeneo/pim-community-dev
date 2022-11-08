<?php

namespace Akeneo\SharedCatalog\Query;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\SharedCatalog\Model\SharedCatalog;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FindProductUuidsQuery implements FindProductUuidsQueryInterface
{
    public function __construct(
        private ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
    ) {
    }

    public function find(SharedCatalog $sharedCatalog, $options = []): array
    {
        $options = $this->resolveOptions($options);

        $pqbOptions = [
            'default_scope' => $sharedCatalog->getDefaultScope(),
            'filters' => $sharedCatalog->getPQBFilters(),
            'limit' => $options['limit'],
        ];

        $searchAfterProductUuid = $options['search_after'];

        if (null !== $searchAfterProductUuid) {
            $pqbOptions['search_after'] = [
                'product_'.$searchAfterProductUuid,
            ];
        }

        $pqb = $this->productQueryBuilderFactory->create($pqbOptions);
        $pqb->addSorter('id', Directions::ASCENDING);

        $results = $pqb->execute();

        return array_map(
            static fn (IdentifierResult $result) => preg_replace('/^product_/', '', $result->getId()),
            iterator_to_array($results)
        );
    }

    private function resolveOptions(array $options = []): array
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired([
            'limit',
        ]);
        $resolver->setDefined([
            'search_after',
        ]);
        $resolver->setDefaults([
            'search_after' => null,
        ]);
        $resolver->setAllowedTypes('search_after', ['string', 'null']);
        $resolver->setAllowedTypes('limit', 'int');

        return $resolver->resolve($options);
    }
}
