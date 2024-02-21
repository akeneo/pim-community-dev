<?php

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductUuidsWithRemovedAttributeInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

final class GetProductUuidsWithRemovedAttribute implements GetProductUuidsWithRemovedAttributeInterface
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
                'document_type' => ProductInterface::class,
            ],
        ]);
        $this->searchQueryBuilder->addFilter([
            'terms' => [
                'attributes_for_this_level' => $attributesCodes,
            ],
        ]);

        $this->searchQueryBuilder->addSort([
            'id' => 'asc',
        ]);

        $body = array_merge(['size' => $batchSize, '_source' => ['id']], $this->searchQueryBuilder->getQuery());

        $rows = $this->elasticsearchClient->search($body);

        while (!empty($rows['hits']['hits'])) {
            $uuids = array_map(function (array $product) {
                return \str_replace('product_', '', $product['_source']['id']);
            }, $rows['hits']['hits']);
            yield $uuids;
            $body['search_after'] = end($rows['hits']['hits'])['sort'];
            $rows = $this->elasticsearchClient->search($body);
        }
    }

    public function getQueryBuilder(): SearchQueryBuilder
    {
        return $this->searchQueryBuilder;
    }
}
