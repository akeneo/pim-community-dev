<?php

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsWithRemovedAttributeInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

final class CountProductsWithRemovedAttribute implements CountProductsWithRemovedAttributeInterface
{
    private SearchQueryBuilder $searchQueryBuilder;

    public function __construct(
        private Client $elasticsearchClient
    ) {
        $this->searchQueryBuilder = new SearchQueryBuilder();
    }

    public function count(array $attributesCodes): int
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

        $body = $this->searchQueryBuilder->getQuery();
        unset($body['_source']);
        unset($body['sort']);

        $result = $this->elasticsearchClient->count($body);

        return (int)$result['count'];
    }

    public function getQueryBuilder(): SearchQueryBuilder
    {
        return $this->searchQueryBuilder;
    }
}
