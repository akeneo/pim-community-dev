<?php

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelIdentifiersWithRemovedAttributeInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

final class GetProductModelIdentifiersWithRemovedAttribute implements GetProductModelIdentifiersWithRemovedAttributeInterface
{
    private SearchQueryBuilder $searchQueryBuilder;

    public function __construct(
        private Client $elasticsearchClient
    ) {
        $this->searchQueryBuilder = new SearchQueryBuilder();
    }

    public function nextBatch(array $attributesCodes, int $batchSize): iterable
    {
        $this->searchQueryBuilder->addFilter([
            'term' => [
                'document_type' => ProductModelInterface::class,
            ],
        ]);
        $this->searchQueryBuilder->addFilter([
            'terms' => [
                'attributes_for_this_level' => $attributesCodes,
            ],
        ]);

        $this->searchQueryBuilder->addSort([
            'identifier' => 'asc',
        ]);

        $body = \array_merge(['size' => $batchSize, '_source' => 'identifier'], $this->searchQueryBuilder->getQuery());

        $rows = $this->elasticsearchClient->search($body);

        while (!empty($rows['hits']['hits'])) {
            $identifiers = array_map(function (array $product) {
                return $product['_source']['identifier'];
            }, $rows['hits']['hits']);
            yield $identifiers;
            $body['search_after'] = end($rows['hits']['hits'])['sort'];
            $rows = $this->elasticsearchClient->search($body);
        }
    }

    public function getQueryBuilder(): SearchQueryBuilder
    {
        return $this->searchQueryBuilder;
    }
}
