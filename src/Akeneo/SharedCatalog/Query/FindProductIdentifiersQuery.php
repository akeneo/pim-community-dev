<?php

namespace Akeneo\SharedCatalog\Query;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\SharedCatalog\Model\SharedCatalog;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FindProductIdentifiersQuery implements FindProductIdentifiersQueryInterface
{
    public function __construct(
        private GetProductIdFromProductIdentifierQueryInterface $getProductIdFromProductIdentifierQuery,
        private ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
    ) {
    }

    public function find(SharedCatalog $sharedCatalog, array $options = []): array
    {
        $options = $this->resolveOptions($options);

        $pqbOptions = [
            'default_scope' => $sharedCatalog->getDefaultScope(),
            'filters' => $sharedCatalog->getPQBFilters(),
            'limit' => $options['limit'],
        ];

        $searchAfterProductIdentifier = $options['search_after'];

        if (null !== $searchAfterProductIdentifier) {
            $searchAfterProductId = $this->getProductIdFromProductIdentifierQuery->execute($searchAfterProductIdentifier);

            if (null === $searchAfterProductId) {
                throw new \InvalidArgumentException(sprintf(
                    'Product with identifier "%s" not found',
                    $searchAfterProductIdentifier
                ));
            }

            $pqbOptions['search_after'] = [
                strtolower($searchAfterProductIdentifier),
                'product_'.$searchAfterProductId,
            ];
        }

        $pqb = $this->productQueryBuilderFactory->create($pqbOptions);
        $pqb->addSorter('identifier', Directions::ASCENDING);

        $results = $pqb->execute();

        return array_map(static fn (IdentifierResult $result) => $result->getIdentifier(), iterator_to_array($results));
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
